<?php

use CRM_Pricesetfrequency_ExtensionUtil as E;

/**
 *
 * Class CRM_Pricesetfrequency_Contribution
 *
 * @see pricesetfrequency_civicrm_postSave_civicrm_contribution
 */
class CRM_Pricesetfrequency_Contribution {

  /**
   * @var bool success or not
   */
  public $isFinished = FALSE;

  /**
   * @var bool getting any error during processing?
   */
  public $isError = FALSE;

  private $sourceContribution;

  private $sourceContributionRecur;

  /**
   * @var array lineItems is in structure of
   *  [unit => [interval => lineItems, ...], ..., one-off => lineItems]
   */
  private $lineItems;

  /**
   * @var array processed contributions - no usage yet
   */
  private $contributions = [];

  /**
   * @var int number of total line items
   */
  private $lineItemCount;

  private $lineItemsGroupCount = 0;

  private $lineItemProcessed = 0;


  /**
   * CRM_Pricesetfrequency_Contribution constructor.
   *
   * @param $contributionID
   */
  public function __construct($contributionID) {
    $this->initialize($contributionID);
    if ($this->isScheduleCorrect()) {
      $this->isFinished = TRUE;
    }
  }

  /**
   * The logic for each group:
   * 1. create/update the contribution and link the corresponding line items
   * 2. create/update the recurring contribution
   * 3. link membership to the correct recurring contribution for auto-renew
   * 4. send receipt for extra contributions
   */
  public function process() {
    if ($this->lineItemsGroupCount == 1) {
      $this->isFinished = TRUE;
      return;
    }

    /* Reset parameters for recurringNotify */
    $autoRenewMembership = FALSE;
    $recurContributionId = 0;

    /*
    Because the last thing to be processed will replace the source contribution,
    we don't want the source contribution be replaced when there are recurring
    item in the set.
    */
    if ($this->lineItemsGroupCount > 1 && $this->lineItems['one-off']) {
      $one_off = $this->lineItems['one-off'];
      $this->lineItems = ['one-off' => $one_off] + $this->lineItems;
    }

    try {
      civicrm_api3('Contribution', 'sendconfirmation', [
        'id' => $this->sourceContribution['id'],
        'payment_processor_id' => ($this->sourceContributionRecur['payment_processor_id'] ?? NULL),
        'receipt_update' => 1,
      ]);
      Civi::$statics[E::LONG_NAME]['receipt_sent'][$this->sourceContribution['id']] = TRUE;
    } catch (CiviCRM_API3_Exception $e) {
      $this->logError('Failed to send receipt.', $e);
    }

    foreach ($this->lineItems as $unit => $value) {
      if ($unit == 'one-off') {
        $contribution = $this->processContribution($value);
        if ($contribution) {
          $contribution = $this->processOneOffContribution($contribution);
          $this->processMembership($contribution, $value);
        }
      }
      else {
        foreach ($value as $interval => $lineItems) {
          $contribution = $this->processContribution($lineItems);
          if ($contribution) {
            $contribution = $this->processRecurringContribution($unit, $interval, $contribution);
            $memberships = $this->processMembership($contribution, $lineItems);
            if((!$recurContributionId || !$autoRenewMembership) && count($memberships)) {
              $autoRenewMembership = TRUE;
              $recurContributionId = $contribution['contribution_recur_id'];
            }
            elseif (!$recurContributionId && !$autoRenewMembership) {
              $recurContributionId = $contribution['contribution_recur_id'];
            }
          }
        }
      }
    }

    $this->isFinished = TRUE;

    Civi::$statics[E::LONG_NAME]['defer_recurringNotify'] = FALSE;

    if($recurContributionId) {
      $recurringContribution = new CRM_Contribute_BAO_ContributionRecur();
      $recurringContribution->id = $recurContributionId;
      $recurringContribution->find(true);

      CRM_Contribute_BAO_ContributionPage::recurringNotify(
        CRM_Core_Payment::RECURRING_PAYMENT_START,
        $contribution['contact_id'],
        $contribution['contribution_page_id'] ?? NULL,
        $recurringContribution,
        $autoRenewMembership
      );
    }

    $this->updateActivitySubject();
  }

  /**
   * This was used to prevent infinity loop, but it is now done by static field
   * check Still using this function to save computation power, however, safe
   * to remove it
   *
   * @return bool
   */
  private function isScheduleCorrect() {
    // true if the contribution only has one schedule and is correctly set
    if ($this->lineItems && count($this->lineItems) === 1) {
      if ($this->lineItems['one-off']) {
        return empty($this->sourceContribution['contribution_recur_id']);
      }
      elseif (count(reset($this->lineItems)) === 1) {
        if (!$this->sourceContributionRecur) {
          return FALSE;
        }
        // lineItems is in structure of [unit => [interval => lineItems, ...], ...]
        $value = reset($this->lineItems);
        $unit = key($this->lineItems);
        reset($value);
        $interval = key($value);
        return $this->sourceContributionRecur['frequency_unit'] == $unit
          && $this->sourceContributionRecur['frequency_interval'] == $interval;
      }
    }
    return FALSE;
  }

  /**
   * Prepare the source contribution and other related resources
   * The line item grouping happens here
   *
   * @param $contributionID int|string
   */
  private function initialize($contributionID) {
    // contribution
    try {
      $contribution = civicrm_api3('Contribution', 'getsingle', [
        'id' => $contributionID,
      ]);
      $contributionAddtionalFields = civicrm_api3('Contribution', 'getsingle', [
        'id' => $contributionID,
        'return' => ['contribution_page_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      $this->logError('Failed to get the contribution.', $e);
      return;
    }
    $contribution = array_merge($contribution, $contributionAddtionalFields);
    $this->sourceContribution = $contribution;
    if ($this->sourceContribution['contribution_status'] !== 'Completed'
      || empty($this->sourceContribution['contribution_page_id'])) {
      // do nothing when the contribution is not completed or not from a contribution page
      $this->isFinished = TRUE;
      return;
    }

    // line items
    try {
      $lineItems = civicrm_api3('LineItem', 'get', [
        'sequential' => 1,
        'contribution_id' => $this->sourceContribution['id'],
        'price_field_id' => ['!=' => '1'],
        'api.PricesetIndividualContribution.getsingle' => [
          'price_field_id' => "\$value.price_field_id",
          'price_field_value_id' => "\$value.price_field_value_id",
        ],
        'options' => ['limit' => 0],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      $this->logError('Failed to get the line items.', $e);
      return;
    }

    if (!$lineItems['count']) {
      // do nothing if the contribution is not using a price set
      $this->isFinished = TRUE;
      return;
    }

    // line item grouping
    $lineItemsGroup = [];
    $this->lineItemCount = $lineItems['count'];
    foreach ($lineItems['values'] as $key => $lineItem) {
      if (!$lineItem['api.PricesetIndividualContribution.getsingle']['is_error']) {
        $unit = $lineItem['api.PricesetIndividualContribution.getsingle']['recurring_contribution_unit'];
        $interval = $lineItem['api.PricesetIndividualContribution.getsingle']['recurring_contribution_interval'];
        if ($unit && $interval) {
          $lineItemsGroup[$unit][$interval][] = $lineItem;
          continue;
        }
      }
      $lineItemsGroup['one-off'][] = $lineItem;
    }
    $this->lineItems = $lineItemsGroup;
    foreach ($lineItemsGroup as $frequency => $value) {
      if ($frequency == 'one-off') {
        $this->lineItemsGroupCount++;
      } else {
        $this->lineItemsGroupCount += count($value);
      }
    }

    // recurring contribution
    if (!empty($this->sourceContribution['contribution_recur_id'])) {
      try {
        $recurringContribution = civicrm_api3('ContributionRecur', 'getsingle', [
          'id' => $this->sourceContribution['contribution_recur_id'],
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        $this->logError('Failed to get the recurring contribution.', $e);
        return;
      }
      $this->sourceContributionRecur = $recurringContribution;
    }
  }

  /**
   * The main logic is in this function
   *
   * @param $lineItems array
   *
   * @return array|null
   */
  private function processContribution($lineItems) {
    $contribution = $this->sourceContribution;
    if (!$this->isLastContribution(count($lineItems))) {
      // udpate the source contribution for the last items
      unset($contribution['id']);
      unset($contribution['contribution_id']);
      unset($contribution['contribution_recur_id']);
    }
    unset($contribution['invoice_id']);
    unset($contribution['trxn_id']);
    // sum the total and tax for all line items
    $sum = [
      'tax' => 0,
      'total' => 0,
      'source' => '',
    ];
    foreach ($lineItems as $lineItem) {
      $sum['tax'] += floatval($lineItem['tax_amount']);
      $sum['total'] += floatval($lineItem['line_total']);
      $sum['source'] .= $lineItem['api.PricesetIndividualContribution.getsingle']['contribution_source'] ?? "";
    }

    $contribution['total_amount'] = $sum['total'] + $sum['tax'];
    $contribution['net_amount'] = $sum['total'];
    $contribution['tax_amount'] = $sum['tax'];
    if ($sum['source']) {
      $contribution['contribution_source'] = $sum['source'];
    }

    $contribution['skipLineItem'] = 1;
    $contribution['financial_type_id'] = $lineItems[0]['financial_type_id'];

    try {
      $contribution = civicrm_api3('Contribution', 'create', $contribution);
      $contribution = reset($contribution['values']);
    } catch (CiviCRM_API3_Exception $e) {
      $this->logError('Failed to create contribution.', $e);
      return NULL;
    }
    $contribution['is_membership'] = FALSE;
    $this->storeContribution($contribution);

    // update line items to link to the new contribution
    foreach ($lineItems as $lineItem) {
      CRM_Core_DAO::setFieldValue('CRM_Price_DAO_LineItem', $lineItem['id'], 'contribution_id', $contribution['id']);
      CRM_Core_DAO::setFieldValue('CRM_Price_DAO_LineItem', $lineItem['id'], 'entity_id', $contribution['id']);
      $contribution['_is_membership'] |= ('civicrm_membership' == $lineItem['entity_table']);
      $this->lineItemProcessed++;
    }

    return $contribution;
  }

  /**
   * Unlink and cancel the recurring contribution here
   *
   * @param $contribution array
   *
   * @return array|mixed
   */
  private function processOneOffContribution($contribution) {
    if (!$contribution['contribution_recur_id']) {
      return $contribution;
    }
    $contributionRecurID = $contribution['contribution_recur_id'];
    unset($contribution['contribution_recur_id']);
    try {
      $contribution = civicrm_api3('Contribution', 'create', $contribution);
      $contribution = reset($contribution['values']);
      if ($contributionRecurID) {
        civicrm_api3('ContributionRecur', 'create', [
          'id' => $contributionRecurID,
          'contribution_status_id' => 'Cancelled',
        ]);
      }
    } catch (CiviCRM_API3_Exception $e) {
      $this->logError('Failed to unset the recurring for one-off contribution.', $e);
      return NULL;
    }
    $this->storeContribution($contribution);

    return $contribution;
  }

  /**
   * Update the schedule or create new recurring contribution
   *
   * @param $unit string
   * @param $interval string|int
   * @param $contribution array
   *
   * @return array|mixed|null
   */
  private function processRecurringContribution($unit, $interval, $contribution) {
    $contributionRecur = $this->sourceContributionRecur;
    if (!$contribution['contribution_recur_id']) {
      // unset id to create new record
      unset($contributionRecur['id']);
    }
    unset($contributionRecur['trxn_id']);
    unset($contributionRecur['invoice_id']);
    $contributionRecur['amount'] = $contribution['total_amount'];
    $contributionRecur['frequency_unit'] = $unit;
    $contributionRecur['frequency_interval'] = $interval;
    $contributionRecur['auto_renew'] = $contribution['_is_membership'];
    // next billing date
    try {
      $nextDate = new DateTime(date('Y-m-d 00:00:00'));
    } catch (Exception $e) {
      $this->logError('Failed to get the next scheduled time.', $e);
      return NULL;
    }
    $nextDate->modify("+{$interval} {$unit}s");
    $contributionRecur['next_sched_contribution_date'] = $nextDate->format("Y-m-d H:i:s");
    $contributionRecur['next_sched_contribution'] = $nextDate->format("Y-m-d H:i:s");

    try {
      $contributionRecur = civicrm_api3('ContributionRecur', 'create', $contributionRecur);
      $contributionRecur = array_shift($contributionRecur['values']);
    } catch (CiviCRM_API3_Exception $e) {
      $this->logError('Failed to create recurring contribution.', $e);
      return NULL;
    }
    $contribution['contribution_recur_id'] = $contributionRecur['id'];
    try {
      $contribution = civicrm_api3('Contribution', 'create', $contribution);
      $contribution = reset($contribution['values']);
    } catch (CiviCRM_API3_Exception $e) {
      $this->logError('Failed to create contribution.', $e);
      return NULL;
    }
    $this->storeContribution($contribution);
    return $contribution;
  }

  /**
   * Update the membership auto-renew information
   * The contribution should in the final state
   *
   * @param $contribution array
   * @param $lineItems array
   *
   * @return array|mixed|null
   */
  private function processMembership($contribution, $lineItems) {
    $memberships = [];
    foreach ($lineItems as $lineItem) {
      if ($lineItem['entity_table'] != 'civicrm_membership' || !$lineItem['entity_id']) {
        continue;
      }
      try {
        $membership = civicrm_api3('Membership', 'getsingle', [
          'id' => $lineItem['entity_id'],
        ]);
        $membershipPayment = civicrm_api3('MembershipPayment', 'get', [
          'membership_id' => $lineItem['entity_id'],
          'contribution_id' => $this->sourceContribution['id'],
          'options' => ['limit' => 0],
        ]);
      } catch (CiviCRM_API3_Exception $e) {
        $this->logError('Failed to get the membership.', $e);
        continue;
      }
      if ($membershipPayment['count']) {
        foreach ($membershipPayment['values'] as $mp) {
          if ($mp['contribution_id'] != $contribution['id']) {
            $mp['contribution_id'] = $contribution['id'];
            try {
              civicrm_api3('MembershipPayment', 'create', $mp);
            } catch (CiviCRM_API3_Exception $e) {
              $this->logError('Failed to update membership payment.', $e);
              continue;
            }
          }
        }
      }
      if ($membership['contribution_recur_id'] == $contribution['contribution_recur_id']) {
        $memberships[] = $membership;
        continue;
      }
      $membership['contribution_recur_id'] = $contribution['contribution_recur_id'] ?? NULL;
      try {
        $membership = civicrm_api3('Membership', 'create', $membership);
        $membership = reset($membership['values']);
      } catch (CiviCRM_API3_Exception $e) {
        $this->logError('Failed to update the membership.', $e);
        continue;
      }
      $memberships[] = $membership;
    }
    return $memberships;
  }

  /**
   * Check if it is the last contribution for the grouped line items
   *
   * @param $lineItemCount int
   *
   * @return bool
   */
  private function isLastContribution($lineItemCount) {
    return $this->lineItemProcessed + $lineItemCount >= $this->lineItemCount;
  }

  /**
   * Store processed contribution in the object field
   * The stored contributions have no usage yet
   *
   * @param $contribution array
   */
  private function storeContribution($contribution) {
    if ($contribution['id']) {
      $this->contributions[$contribution['id']] = $contribution;
    }
  }

  /**
   * Log to civi log
   *
   * @param $message string
   * @param $e \Exception
   */
  private function logError($message, $e) {
    Civi::log()->alert($message);
    Civi::log()->error($e->getMessage());
    $this->isError = TRUE;
  }

  /**
   * Update the subject line of the initially created Activity.
   */
  private function updateActivitySubject() {
    $contribution = new CRM_Contribute_BAO_Contribution();
    $contribution->id = $this->sourceContribution['id'];
    if($contribution->find(TRUE)) {
      $activityParams = [ 'source_record_id' => $contribution->id,
                          'activity_type_id' => 'Contribution' ];
      try {
        $activity = civicrm_api3('Activity', 'getsingle', $activityParams);
        $subject = CRM_Activity_BAO_Activity::getActivitySubject($contribution);
        Civi::$statics[E::LONG_NAME]['activity_subject'][$activity['id']] = $subject;
      }
      catch(CiviCRM_API3_Exception $e) {
        Civi::log()->warning('Could not update activity subject',
                             [ 'message' => $e->getMessage(),
                               'trace'   => CRM_Core_Error::formatBacktrace($e->getTrace()) ]);
      }
    }
  }
}
