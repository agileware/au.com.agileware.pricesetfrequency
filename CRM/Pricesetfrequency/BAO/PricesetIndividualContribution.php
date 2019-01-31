<?php
use CRM_Pricesetfrequency_ExtensionUtil as E;

class CRM_Pricesetfrequency_BAO_PricesetIndividualContribution extends CRM_Pricesetfrequency_DAO_PricesetIndividualContribution {

  /**
   * Create a new PricesetIndividualContribution based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Pricesetfrequency_DAO_PricesetIndividualContribution|NULL
   *
  public static function create($params) {
    $className = 'CRM_Pricesetfrequency_DAO_PricesetIndividualContribution';
    $entityName = 'PricesetIndividualContribution';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
