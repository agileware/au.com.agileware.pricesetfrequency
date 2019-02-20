<?php

class CRM_Pricesetfrequency_APIWrappers_ContributionTransactionWrapper implements API_Wrapper {

  /**
   * Interface for interpreting api input.
   *
   * @param array $apiRequest
   *
   * @return array
   *   modified $apiRequest
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * Interface for interpreting api output.
   *
   * @param array $apiRequest
   * @param array $result
   *
   * @return array
   *   modified $result
   */
  public function toApiOutput($apiRequest, $result) {
    $contributionRecurId = $result['values'][$result['id']]['contribution_recur_id'];
    try {
      civicrm_api3('ContributionRecur', 'getsingle', array(
        'id'                     => $contributionRecurId,
        'contribution_status_id' => 'Cancelled',
        'frequency_interval'     => 0,
      ));

      civicrm_api3('ContributionRecur', 'delete', array(
        'id' => $contributionRecurId,
      ));
    }
    catch (CiviCRM_API3_Exception $e) {

    }
    return $result;
  }

}
