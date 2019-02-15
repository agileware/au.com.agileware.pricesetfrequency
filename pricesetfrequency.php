<?php

require_once 'pricesetfrequency.civix.php';
use CRM_Pricesetfrequency_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function pricesetfrequency_civicrm_config(&$config) {
  _pricesetfrequency_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function pricesetfrequency_civicrm_xmlMenu(&$files) {
  _pricesetfrequency_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function pricesetfrequency_civicrm_install() {
  _pricesetfrequency_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function pricesetfrequency_civicrm_postInstall() {
  _pricesetfrequency_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function pricesetfrequency_civicrm_uninstall() {
  _pricesetfrequency_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function pricesetfrequency_civicrm_enable() {
  _pricesetfrequency_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function pricesetfrequency_civicrm_disable() {
  _pricesetfrequency_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function pricesetfrequency_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _pricesetfrequency_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function pricesetfrequency_civicrm_managed(&$entities) {
  _pricesetfrequency_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function pricesetfrequency_civicrm_caseTypes(&$caseTypes) {
  _pricesetfrequency_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function pricesetfrequency_civicrm_angularModules(&$angularModules) {
  _pricesetfrequency_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function pricesetfrequency_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _pricesetfrequency_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function pricesetfrequency_civicrm_entityTypes(&$entityTypes) {
  _pricesetfrequency_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Add contribution fields on the form.
 * @param $form
 */
function addContributionFormFields(&$form) {
  $templatePath = realpath(dirname(__FILE__) . "/templates");

  $form->add('checkbox', 'create_individual_contribution', ts('Create Individual Contribution'));

  $units = array(
    ''      => ts('- select -'),
    'day'   => ts('day'),
    'week'  => ts('week'),
    'month' => ts('month'),
    'year'  => ts('year'),
  );
  $form->add('select', 'recurring_contribution_unit', ts('Recurring Contribution Unit'), $units);
  $form->add('text', 'recurring_contribution_interval', ts('Recurring Contribution Interval'));

  for ($i = 1; $i <= 15; $i++) {
    $form->add('checkbox', 'option_create_individual_contribution[' . $i . ']', ts('Create Individual Contribution'));
    $form->add('select', 'option_recurring_contribution_unit[' . $i . ']', ts('Recurring Contribution Unit'), $units);
    $form->add('text', 'option_recurring_contribution_interval[' . $i . ']', ts('Recurring Contribution Interval'), array('size' => 5, 'maxlength' => 3));
  }

  $defaults['recurring_contribution_interval'] = 1;
  $form->setDefaults($defaults);

  CRM_Core_Region::instance('page-body')->add(array(
    'template' => "{$templatePath}/pricefield-inputs.tpl",
  ));

  $optionId = $form->getVar('_oid');
  $fieldId = $form->getVar('_fid');

  try {
    $priceFieldExtras = civicrm_api3('PricesetIndividualContribution', 'getsingle', array(
      'price_field_value_id' => $optionId,
      'price_field_id' => $fieldId,
      'sequential'     => TRUE,
    ));
    setPriceSetContributionDefaultValues($priceFieldExtras, $form);
  }
  catch (CiviCRM_API3_Exception $e) {

  }
}

/**
 * Add contribution related fields on Option value form and price field form.
 * @param $formName
 * @param $form
 * @throws CiviCRM_API3_Exception
 */
function pricesetfrequency_civicrm_buildForm($formName, &$form) {
  if ($form->_action != CRM_Core_Action::DELETE) {
    if ($formName == "CRM_Price_Form_Option") {
      addContributionFormFields($form);
    }

    if ($formName == 'CRM_Price_Form_Field') {
      addContributionFormFields($form);
      $priceSetId = $form->getVar('_fid');
      if ($priceSetId) {
        $priceFieldExtras = civicrm_api3('PricesetIndividualContribution', 'get', array(
          'price_field_id' => $priceSetId,
          'sequential'     => TRUE,
        ));
        $priceFieldExtras = $priceFieldExtras['values'];

        $htmlType = $form->_defaultValues['html_type'];

        if (count($priceFieldExtras) > 0) {
          if ($htmlType == "Text") {
            setPriceSetContributionDefaultValues($priceFieldExtras[0], $form);
          }
        }
      }
    }
  }

}

/**
 * Set default values of contribution fields on edit form.
 * @param $priceFieldExtras
 * @param $form
 */
function setPriceSetContributionDefaultValues($priceFieldExtras, &$form) {
  if (!isset($priceFieldExtras['create_individual_contribution'])) {
    $priceFieldExtras['create_individual_contribution'] = 0;
  }
  $defaults['create_individual_contribution'] = $priceFieldExtras['create_individual_contribution'];
  $defaults['recurring_contribution_unit'] = $priceFieldExtras['recurring_contribution_unit'];
  $defaults['recurring_contribution_interval'] = $priceFieldExtras['recurring_contribution_interval'];
  $form->setDefaults($defaults);
}

/**
 * Validate single contribution field on form submit.
 * @param $fields
 * @param $errors
 * @throws CRM_Core_Exception
 */
function validateSingleContributionFormFields($fields, &$errors) {
  $recurringInterval = CRM_Utils_Array::value('recurring_contribution_interval', $fields);

  if ($recurringInterval != '' && (!CRM_Utils_Type::validate($recurringInterval, 'Int', FALSE, ts('Recurring Contribution Interval')) || $recurringInterval < 1)) {
    $errors['recurring_contribution_interval'] = ts('Recurring Contribution Interval must be a number greater than 1.');
  }
}

/**
 * Validate option value form and price field form.
 *
 * @param $formName
 * @param $fields
 * @param $files
 * @param $form
 * @param $errors
 * @throws CRM_Core_Exception
 */
function pricesetfrequency_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($form->_action != CRM_Core_Action::DELETE) {
    if ($formName == "CRM_Price_Form_Option") {
      validateSingleContributionFormFields($fields, $errors);
    }

    if ($formName == 'CRM_Price_Form_Field') {
      if ($fields['html_type'] == 'Text') {
        validateSingleContributionFormFields($fields, $errors);
      }
      else {
        for ($i = 1; $i <= 15; $i++) {
          $optionLabel = $form->getSubmitValue('option_label[' . $i . ']');
          $optionAmount = $form->getSubmitValue('option_amount[' . $i . ']');

          if ($optionLabel != '' && $optionAmount != '') {

            $recurringInterval = $form->getSubmitValue('option_recurring_contribution_interval[' . $i . ']');
            if ($recurringInterval != '' && (!CRM_Utils_Type::validate($recurringInterval, 'Int', FALSE, ts('Recurring Contribution Interval')) || $recurringInterval < 1)) {
              $errors['option_recurring_contribution_interval[' . $i . ']'] = ts('Recurring Contribution Interval must be a number greater than 1.');
            }
          }
        }
      }
    }
  }
}

/**
 * Save contribution related data for price field and option.
 *
 * @param $fieldId
 * @param $optionId
 * @param $form
 * @throws CiviCRM_API3_Exception
 */
function savePriceFieldOptionExtras($fieldId, $optionId, &$form) {

  $recurringInterval = $form->getSubmitValue('recurring_contribution_interval');
  $recurringUnit = $form->getSubmitValue('recurring_contribution_unit');
  $createIndividualContribution = $form->getSubmitValue('create_individual_contribution');

  if (!isset($createIndividualContribution)) {
    $createIndividualContribution = 0;
  }

  $individualContribution = civicrm_api3('PricesetIndividualContribution', 'get', [
    'price_field_id'       => $fieldId,
    'price_field_value_id' => $optionId,
    'sequential'           => TRUE,
  ]);
  $individualContribution = $individualContribution['values'];

  if (count($individualContribution) > 0) {
    $individualContribution = $individualContribution[0];
  }
  else {
    $individualContribution = array();
  }

  $individualContribution['price_field_id'] = $fieldId;
  $individualContribution['price_field_value_id'] = $optionId;
  $individualContribution['create_individual_contribution'] = $createIndividualContribution;
  $individualContribution['recurring_contribution_unit'] = $recurringUnit;
  $individualContribution['recurring_contribution_interval'] = $recurringInterval;

  civicrm_api3('PricesetIndividualContribution', 'create', $individualContribution);
}

/**
 * Save contribution related data in database for price field and option values.
 *
 * @param $formName
 * @param $form
 * @throws CiviCRM_API3_Exception
 */
function pricesetfrequency_civicrm_postProcess($formName, &$form) {
  if ($form->_action != CRM_Core_Action::DELETE) {
    if ($formName == "CRM_Price_Form_Option" && isset(Civi::$statics['inserted_price_field_value_id'])) {

      $fieldId = $form->getVar('_fid');
      $optionId = Civi::$statics['inserted_price_field_value_id'];
      savePriceFieldOptionExtras($fieldId, $optionId, $form);
    }

    if ($formName == 'CRM_Price_Form_Field' && isset(Civi::$statics['inserted_price_field_id'])) {

      $htmlType = $form->getSubmitValue('html_type');
      $priceFieldId = Civi::$statics['inserted_price_field_id'];

      $priceFieldValues = civicrm_api3('PriceFieldValue', 'get', [
        'sequential'     => 1,
        'price_field_id' => $priceFieldId,
      ]);
      $priceFieldValues = $priceFieldValues['values'];

      if ($htmlType == 'Text') {
        foreach ($priceFieldValues as $priceFieldValue) {
          savePriceFieldOptionExtras($priceFieldId, $priceFieldValue['id'], $form);
        }
      }
      else {
        $valueIndex = 0;
        for ($i = 1; $i <= 15; $i++) {
          $optionLabel = $form->getSubmitValue('option_label[' . $i . ']');
          $optionAmount = $form->getSubmitValue('option_amount[' . $i . ']');

          if ($optionLabel != '' && $optionAmount != '') {

            $createIndividualContribution = $form->getSubmitValue('option_create_individual_contribution[' . $i . ']');
            $recurringUnit = $form->getSubmitValue('option_recurring_contribution_unit[' . $i . ']');
            $recurringInterval = $form->getSubmitValue('option_recurring_contribution_interval[' . $i . ']');

            if (!isset($createIndividualContribution)) {
              $createIndividualContribution = 0;
            }

            if (isset($priceFieldValues[$valueIndex])) {
              $priceFieldValue = $priceFieldValues[$valueIndex];

              civicrm_api3('PricesetIndividualContribution', 'create', array(
                'price_field_id' => $priceFieldId,
                'price_field_value_id' => $priceFieldValue['id'],
                'create_individual_contribution' => $createIndividualContribution,
                'recurring_contribution_unit' => $recurringUnit,
                'recurring_contribution_interval' => $recurringInterval,
              ));

            }

            $valueIndex++;
          }
        }
      }
    }
  }
}

/**
 * Save the last inserted price field id in statics.
 * @param $dao
 */
function pricesetfrequency_civicrm_postSave_civicrm_price_field($dao) {
  Civi::$statics['inserted_price_field_id'] = $dao->id;
}

/**
 * Save the last insert price field value id in statics.
 * @param $dao
 */
function pricesetfrequency_civicrm_postSave_civicrm_price_field_value($dao) {
  Civi::$statics['inserted_price_field_value_id'] = $dao->id;
}

/**
 * Save the last insert price field value id in statics.
 * @param $dao
 */
function pricesetfrequency_civicrm_postSave_civicrm_contribution($dao) {
  $contribution = civicrm_api3('Contribution', 'get', array(
    'id'         => $dao->id,
    'return'     => ["contribution_source", "contact_id", "financial_type_id", "contribution_page_id", "payment_instrument_id", "receive_date", "non_deductible_amount", "total_amount", "fee_amount", "net_amount", "trxn_id", "invoice_id", "currency", "cancel_date", "cancel_reason", "receipt_date", "thankyou_date", "source", "amount_level", "contribution_recur_id", "is_test", "is_pay_later", "contribution_status_id", "address_id", "check_number", "campaign_id", "creditnote_id", "tax_amount", "revenue_recognition_date"],
    'sequential' => TRUE,
  ));

  if (count($contribution['values']) == 0) {
    return;
  }

  $contribution = $contribution['values'][0];

  $mainContributionUpdated = FALSE;
  $updatedLineItems = FALSE;
  $processedContributions = array();
  $mainRecurringContributionUpdated = FALSE;

  if ($contribution['contribution_status'] == 'Completed' && isset($contribution['contribution_page_id']) && $contribution['contribution_page_id'] != '') {

    $lineItems = civicrm_api3('LineItem', 'get', [
      'sequential'      => 1,
      'contribution_id' => $dao->id,
      'return' => ["id", "entity_table", "entity_id", "contribution_id", "price_field_id", "label", "qty", "unit_price", "line_total", "participant_count", "price_field_value_id", "financial_type_id", "non_deductible_amount", "tax_amount"],
    ]);

    $lineItems = $lineItems['values'];
    if (count($lineItems) <= 1) {
      return;
    }

    $recurringContribution = NULL;

    if (isset($contribution['contribution_recur_id']) && $contribution['contribution_recur_id']) {
      $recurringContribution = civicrm_api3('ContributionRecur', 'get', array(
        'id' => $contribution['contribution_recur_id'],
        'sequential' => TRUE,
      ));

      if (count($recurringContribution['values'])) {
        $recurringContribution = $recurringContribution['values'][0];
      }
      else {
        $recurringContribution = NULL;
      }
    }

    foreach ($lineItems as $lineItem) {
      $priceFieldId = $lineItem['price_field_id'];
      $priceFieldValuId = $lineItem['price_field_value_id'];

      try {
        $invidiaulConfig = civicrm_api3('PricesetIndividualContribution', 'getsingle', array(
          'price_field_id' => $priceFieldId,
          'price_field_value_id' => $priceFieldValuId,
        ));
        if (isset($invidiaulConfig['create_individual_contribution']) && $invidiaulConfig['create_individual_contribution']) {
          $newContribution = $contribution;

          $newContribution['total_amount'] = $lineItem['line_total'] + $lineItem['tax_amount'];
          $newContribution['net_amount'] = $newContribution['total_amount'];
          $newContribution['tax_amount'] = $lineItem['tax_amount'];

          if ($newContribution['total_amount'] != $contribution['total_amount']) {
            unset($newContribution['id']);
            unset($newContribution['contribution_id']);
            unset($newContribution['contribution_recur_id']);
            unset($newContribution['invoice_id']);
            unset($newContribution['trxn_id']);

            $contribution['total_amount'] -= $newContribution['total_amount'];
            $contribution['net_amount'] -= $newContribution['net_amount'];
            $contribution['tax_amount'] -= $newContribution['tax_amount'];
          }
          else {
            $mainContributionUpdated = TRUE;
          }

          if ($recurringContribution) {
            $newRecurringContribution = $recurringContribution;

            if (!isset($newContribution['id'])) {
              unset($newRecurringContribution['id']);
              unset($newRecurringContribution['trxn_id']);
              unset($newRecurringContribution['invoice_id']);
            }

            $newRecurringContribution['amount'] = $newContribution['total_amount'];
            $newRecurringContribution['frequency_unit'] = $invidiaulConfig['recurring_contribution_unit'];
            $newRecurringContribution['frequency_interval'] = $invidiaulConfig['recurring_contribution_interval'];

            $nextDate = new DateTime(date('Y-m-d 00:00:00'));
            $nextDate->modify("+{$invidiaulConfig['recurring_contribution_interval']} " .
              "{$invidiaulConfig['recurring_contribution_unit']}s");

            $newRecurringContribution['next_sched_contribution_date'] = $nextDate->format("Y-m-d H:i:s");
            $newRecurringContribution['next_sched_contribution'] = $nextDate->format("Y-m-d H:i:s");

            if (!isset($newContribution['id'])) {
              $recurringContribution['amount'] -= $newRecurringContribution['amount'];
            }
            else {
              $mainRecurringContributionUpdated = TRUE;
            }

            try {
              $newRecurringContribution = civicrm_api3('ContributionRecur', 'create', $newRecurringContribution);
              $newContribution['contribution_recur_id'] = $newRecurringContribution['id'];
            }
            catch (CiviCRM_API3_Exception $e) {

            }

          }

          try {

            $priceFieldObject = civicrm_api3('PriceField', 'getsingle', [
              'return' => ["price_set_id"],
              'id'     => $lineItem['price_field_id'],
            ]);

            if (!isset($newContribution['id'])) {
              $newContribution['line_item'] = array(
                $priceFieldObject['price_set_id'] => array(),
              );
            }

            $newContribution = civicrm_api3('Contribution', 'create', $newContribution);
            $processedContributions[] = $newContribution['id'];

            CRM_Core_DAO::setFieldValue('CRM_Price_DAO_LineItem', $lineItem['id'], 'contribution_id', $newContribution['id']);
            CRM_Core_DAO::setFieldValue('CRM_Price_DAO_LineItem', $lineItem['id'], 'entity_id', $newContribution['id']);

            $updatedLineItems = TRUE;

          }
          catch (CiviCRM_API3_Exception $e) {

          }

        }
      }
      catch (CiviCRM_API3_Exception $e) {
        // Line item configuration not found. Skip!
      }
    }

    if ($recurringContribution && !$mainRecurringContributionUpdated) {
      civicrm_api3('ContributionRecur', 'create', [
        'id'     => $recurringContribution['id'],
        'amount' => $recurringContribution['amount'],
      ]);
    }

    if (!$mainContributionUpdated && $updatedLineItems) {
      civicrm_api3('Contribution', 'create', array(
        'total_amount' => $contribution['total_amount'],
        'tax_amount'   => $contribution['tax_amount'],
        'net_amount'   => $contribution['net_amount'],
        'id'           => $contribution['id'],
      ));
    }
  }
}
