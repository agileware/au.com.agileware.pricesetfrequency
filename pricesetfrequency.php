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

  $form->add('text', 'individual_contribution_source', E::ts('Contribution Source'));

  $units = array(
    ''      => E::ts('No recurrence'),
    'day'   => E::ts('day'),
    'week'  => E::ts('week'),
    'month' => E::ts('month'),
    'year'  => E::ts('year'),
  );
  $form->add('select', 'recurring_contribution_unit', E::ts('Recurring Contribution Unit'), $units);
  $form->add('text', 'recurring_contribution_interval', E::ts('Recurring Contribution Interval'));

  for ($i = 1; $i <= 15; $i++) {
    $form->add('select', 'option_recurring_contribution_unit[' . $i . ']', E::ts('Recurring Contribution Unit'), $units);
    $form->add('text', 'option_recurring_contribution_interval[' . $i . ']', E::ts('Recurring Contribution Interval'), array('size' => 5, 'maxlength' => 3));
    $form->add('text', 'option_individual_contribution_source[' . $i . ']', E::ts('Contribution Source'), array('size' => 10));
  }

  $defaults['recurring_contribution_interval'] = '';
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
 * Get recurring label of price field.
 *
 * @param $priceFieldId
 * @param $totalPriceFields
 * @param $updatedPriceFields
 * @return string
 * @throws CiviCRM_API3_Exception
 */
function getPriceFieldRecurringLabel($priceFieldId, &$totalPriceFields, &$updatedPriceFields, $forValidation = FALSE) {
  $totalPriceFields++;
  $priceFieldExtras = civicrm_api3('PricesetIndividualContribution', 'get', array(
    'price_field_value_id' => $priceFieldId,
    'sequential'     => TRUE,
  ));

  if (count($priceFieldExtras['values']) > 0) {
    $priceFieldExtras = $priceFieldExtras['values'][0];
  }
  else {
    $priceFieldExtras = NULL;
  }

  if ($priceFieldExtras) {
    return getRecurringContributionLabel($priceFieldExtras, $updatedPriceFields, $forValidation);
  }

  return '';
}

/**
 * Update price field element labels on Main Form.
 * @param $elements
 */
function updatePricefieldElements(&$elements, &$totalPriceFields, &$updatedPriceFields, $forValidation = FALSE) {
  foreach ($elements as $index => $element) {
    if (isset($element->_attributes) && isset($element->_attributes['price'])) {
      $priceValue = str_replace("\"", "", trim($element->_attributes['price'], "[]|\""));
      $priceField = explode(",", $priceValue);
      if (strpos($priceField[0], "price_") !== FALSE) {
        if (!($element instanceof HTML_QuickForm_radio)) {
          $priceField[0] = 0;
        }
        else {
          if (isset($element->_attributes) && isset($element->_attributes['value']) && !empty($element->_attributes['value'])) {
            $priceField[0] = $element->_attributes['value'];
          }
          else {
            $priceField[0] = 0;
          }
        }
      }

      if (count($priceField) > 0) {
        if (isset($elements[$index]->_text) && $elements[$index]->_text != '') {
          $elements[$index]->_text .= getPriceFieldRecurringLabel($priceField[0], $totalPriceFields, $updatedPriceFields, $forValidation);
        }
        if (isset($element->_options) && count($element->_options)) {
          foreach ($element->_options as $optionIndex => $elementOption) {
            if (isset($elementOption['attr']) && isset($elementOption['attr']['value']) && $elementOption['attr']['value']) {
              $elements[$index]->_options[$optionIndex]['text'] .= getPriceFieldRecurringLabel($elementOption['attr']['value'], $totalPriceFields, $updatedPriceFields, $forValidation);
            }
          }
        }
        if (isset($elements[$index]->_label) && !isset($element->_options)  && $elements[$index]->_label != '') {
          $elements[$index]->_label .= getPriceFieldRecurringLabel($priceField[0], $totalPriceFields, $updatedPriceFields, $forValidation);
        }
      }
    }
    elseif (($element instanceof HTML_QuickForm_group) && isset($element->_elements)) {
      updatePricefieldElements($element->_elements, $totalPriceFields, $updatedPriceFields, $forValidation);
    }
  }

}

/**
 * Update is recurring text on main form.
 * @param $elements
 * @param $totalPriceFields
 * @param $updatedPriceFields
 */
function updateIsRecurringText(&$elements, &$form, $totalPriceFields, $updatedPriceFields) {
  $recurringIndex = -1;

  foreach ($elements as $index => $element) {
    if (isset($element->_attributes) && isset($element->_attributes['name'])) {
      if ($element->_attributes['name'] == 'is_recur') {
        $recurringIndex = $index;
      }
    }
  }
  if ($recurringIndex > 0) {
    if ($updatedPriceFields > 0) {
      $elements[$recurringIndex]->_label = E::ts('I confirm that the above recurring contributions can be billed to my credit card.');
      $form->assign('one_frequency_unit', 1);
      $form->assign('is_recur_interval', 0);
    }
  }
}

/**
 * Update labels of all line items.
 *
 * @param $lineItems
 * @param $totalPriceFields
 * @param $updatedPriceFields
 * @throws CiviCRM_API3_Exception
 */
function updatePricesetFieldLabels(&$lineItems, &$totalPriceFields, &$updatedPriceFields) {
  foreach ($lineItems as $lineItemId => $priceFields) {
    foreach ($priceFields as $priceFieldId => $priceField) {
      if (isset($priceField['price_field_value_id']) && !empty($priceField['price_field_value_id'])) {
        $lineItems[$lineItemId][$priceFieldId]['label'] .= getPriceFieldRecurringLabel($priceField['price_field_value_id'], $totalPriceFields, $updatedPriceFields);
      }
    }
  }
}

/**
 * Get line items count having individual recurrence.
 *
 * @param $lineItems
 * @param $totalPriceFields
 * @param $updatedPriceFields
 * @throws CiviCRM_API3_Exception
 */
function getLinesItemsCountOfIndividualRecurrence(&$priceSets, &$totalPriceFields, &$updatedPriceFields) {
  foreach ($priceSets as $priceSetId => $priceFields) {
    if (isset($priceFields['options'])) {
      foreach ($priceFields['options'] as $priceFieldOptionId => $priceFieldOption) {
        getPriceFieldRecurringLabel($priceFieldOption['id'], $totalPriceFields, $updatedPriceFields);
      }
    }
  }
}

/**
 * @param $priceFieldExtras
 */
function getRecurringContributionLabel($priceFieldExtras, &$updatedPriceFields, $forValidation = FALSE) {
  if (!isset($priceFieldExtras['recurring_contribution_unit']) || !$priceFieldExtras['recurring_contribution_unit']) {
    return '';
  }

  $interval = $priceFieldExtras['recurring_contribution_interval'];
  $intervalUnits = $priceFieldExtras['recurring_contribution_unit'];
  $updatedPriceFields++;

  if ($interval > 1) {
    $intervalUnits = $intervalUnits . 's';
  }

  if ($forValidation) {
    return '';
  }

  return ' (Contribute every ' . $interval . ' ' . $intervalUnits . ')';
}

/**
 * Alter fields for an event registration to make them into a demo form.
 */
function pricesetfrequency_civicrm_alterContent(&$content, $context, $tplName, &$object) {
  if ($context == "form") {
    if ($tplName == "CRM/Contribute/Form/Contribution/Main.tpl") {
      $content = str_replace(".</label> every", ".</label>", $content);
      $content = str_replace("</span>\n\n</label> every", "</span></label>", $content);
    }
  }
}

/**
 * Set price set configuration for Amounts form.
 *
 * @param $form
 * @throws CiviCRM_API3_Exception
 */
function setPricesetConfiguration(&$form) {
  if (isset($form->_elementIndex['price_set_id']) && $form->_elementIndex['price_set_id']) {
    $priceSetField = $form->_elements[$form->_elementIndex['price_set_id']];
    $priceSets = array_column(array_column($priceSetField->_options, 'attr'), 'value');
    $priceSetIndividualContributions = array();

    checkPriceSetsRecurrence($priceSets, $priceSetIndividualContributions);

    if (isset($priceSetField->_attributes) && isset($priceSetField->_attributes['onchange'])) {
      $priceSetField->_attributes['onchange'] .= ' showHideRecurringBlockBasedOnPriceSet(this.value);';
    }

    $defaultPriceSetId = 0;
    if (isset($form->_defaultValues['price_set_id'])) {
      $defaultPriceSetId = $form->_defaultValues['price_set_id'];
    }

    $hideRecurringSection = FALSE;
    if (in_array($defaultPriceSetId, $priceSetIndividualContributions)) {
      $hideRecurringSection = TRUE;
    }

    $form->assign('isMembershipPriceSetSelected', FALSE);

    if (!$hideRecurringSection) {
      verifyMembershipPriceSets($hideRecurringSection, $priceSetIndividualContributions, $form);
    }

    $form->assign('hideRecurringSection', $hideRecurringSection);
    $form->assign('priceSetIndividualContribution', $priceSetIndividualContributions);
  }
}

/**
 * Check if price set is set for individual recurrence.
 *
 * @param $priceSets
 * @param $priceSetIndividualContributions
 * @throws CiviCRM_API3_Exception
 */
function checkPriceSetsRecurrence($priceSets, &$priceSetIndividualContributions) {
  foreach ($priceSets as $priceSetId) {
    if ($priceSetId) {
      $priceFieldValues = civicrm_api3('PriceFieldValue', 'get', [
        'sequential' => 1,
        'price_field_id.price_set_id.id' => $priceSetId,
      ]);
      $priceFieldValues = $priceFieldValues['values'];
      foreach ($priceFieldValues as $priceFieldValue) {
        $priceSetFieldExtras = civicrm_api3('PricesetIndividualContribution', 'get', [
          'sequential'                     => 1,
          'price_field_id'                 => $priceFieldValue['price_field_id'],
          'price_field_value_id'           => $priceFieldValue['id'],
          'recurring_contribution_unit' => ['!=' => ""],
        ]);
        $priceSetFieldExtras = $priceSetFieldExtras['values'];
        if (count($priceSetFieldExtras) > 0) {
          $priceSetIndividualContributions[] = $priceSetId;
          break;
        }
      }
    }
  }
}

/**
 * Verify membership pricesets.
 *
 * @param $hideRecurringSection
 * @param $priceSetIndividualContributions
 * @param $form
 */
function verifyMembershipPriceSets(&$hideRecurringSection, &$priceSetIndividualContributions, &$form) {
  $membershipPriceSets = civicrm_api3('PriceSet', 'get', [
    'sequential' => 1,
    'return'     => ["id"],
    'extends'    => "CiviMember",
  ]);

  $membershipPriceSets = $membershipPriceSets['values'];
  $membershipPriceSets = array_column($membershipPriceSets, 'id');
  checkPriceSetsRecurrence($membershipPriceSets, $priceSetIndividualContributions);
  $priceSetId = CRM_Price_BAO_PriceSet::getFor('civicrm_contribution_page', $form->_defaultValues['id'], NULL);
  if (in_array($priceSetId, $priceSetIndividualContributions)) {
    $hideRecurringSection = TRUE;
    $form->assign('isMembershipPriceSetSelected', TRUE);
  }
}

/**
 * Set recurring by default if any price field has individual recurring.
 * @param $formName
 * @param $form
 */
function pricesetfrequency_civicrm_preProcess($formName, &$form) {
  if ($formName == "CRM_Contribute_Form_Contribution_Main") {
    if (isset($form->_values) && isset($form->_values['fee'])) {
      $totalPriceFields = 0;
      $updatedPriceFields = 0;
      getLinesItemsCountOfIndividualRecurrence($form->_values['fee'], $totalPriceFields, $updatedPriceFields);
      if ($updatedPriceFields > 0) {
        $form->_values['is_recur'] = 1;
      }
    }
    $form->_expressButtonName = 'eWayRecurring';
  } elseif ($formName == "CRM_Contribute_Form_Contribution_Confirm") {
    // set the frequency of initial recurring contribution to the first line item
    $priceSet = reset($form->_lineItem);
    $firstPriceField = reset($priceSet);
    try {
      $individualConfig = civicrm_api3('PricesetIndividualContribution', 'getsingle', [
        'price_field_id' => $firstPriceField['price_field_id'],
        'price_field_value_id' => $firstPriceField['price_field_value_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
      // don't do anything if not found
      return;
    }
    $form->_params['frequency_interval'] = $individualConfig['recurring_contribution_interval'];
    $form->_params['frequency_unit'] = $individualConfig['recurring_contribution_unit'];
  }
}

/**
 * Set membership interval and duration if auto renew membership type is selected.
 *
 * @param $form
 * @throws CiviCRM_API3_Exception
 */
function setMembershipIntervalAndDuration(&$form) {
  $lineItems = $form->_lineItem;
  foreach ($lineItems as $lineItemId => $priceFields) {
    foreach ($priceFields as $priceFieldId => $priceField) {
      if (isset($priceField['membership_type_id']) && !empty($priceField['membership_type_id']) && ($priceField['auto_renew'] == 1 || $priceField['auto_renew'] == 2)) {
        $membershipType = civicrm_api3('MembershipType', 'get', array(
          'id'         => $priceField['membership_type_id'],
          'sequential' => 1,
        ));
        $membershipType = $membershipType['values'];
        if (count($membershipType)) {
          $membershipType = $membershipType[0];
          $form->_params['frequency_interval'] = $membershipType['duration_interval'];
          $form->_params['frequency_unit'] = $membershipType['duration_unit'];
          return;
        }
      }
    }
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
    $templatePath = realpath(dirname(__FILE__) . "/templates");

    if ($formName == "CRM_Price_Form_Option") {
      addContributionFormFields($form);
    }

    if ($formName == "CRM_Contribute_Form_ContributionPage_Amount") {
      CRM_Core_Region::instance('contribute-form-contributionpage-amount-post')->add(array(
        'template' => "{$templatePath}/contribution-page-amount.tpl",
      ));
      setPricesetConfiguration($form);
    }

    if ($formName == "CRM_Contribute_Form_Contribution_Confirm" || $formName == "CRM_Contribute_Form_Contribution_ThankYou") {
      $updatedPriceFields = 0;
      $totalPriceFields = 0;
      $lineItems = $form->_lineItem;
      updatePricesetFieldLabels($lineItems, $totalPriceFields, $updatedPriceFields);
      // Replace the section that displays the recurring schedule.
      // We have the schedule displayed on each line item
      if ($updatedPriceFields) {
        CRM_Core_Region::instance('contribution-thankyou-recur')->update('default', [
          'disabled' => TRUE,
        ]);
        CRM_Core_Region::instance('contribution-thankyou-recur')->add([
          'template' => "{$templatePath}/contribution-thankyou-recur-region.tpl"
        ]);
        CRM_Core_Region::instance('contribution-confirm-recur')->update('default', [
          'disabled' => TRUE,
        ]);
        CRM_Core_Region::instance('contribution-confirm-recur')->add([
          'template' => "{$templatePath}/contribution-confirm-recur-region.tpl"
        ]);
      }
      $form->_lineItem = $lineItems;
      $form->assign('lineItem', $lineItems);
      if ($formName == "CRM_Contribute_Form_Contribution_Confirm") {
        if (isset($form->_params['auto_renew']) && $form->_params['auto_renew']) {
          setMembershipIntervalAndDuration($form);
        }
      }
    }

    if ($formName == "CRM_Contribute_Form_Contribution_Main") {
      $elements = $form->_elements;
      $updatedPriceFields = 0;
      $totalPriceFields = 0;
      updatePricefieldElements($elements, $totalPriceFields, $updatedPriceFields);
      updateIsRecurringText($elements, $form, $totalPriceFields, $updatedPriceFields);
      if ($updatedPriceFields) {
        $defaults = array();
        // this will be replaced in the preProcess hook of contribution confirm form
        $defaults['frequency_interval'] = 1;
        $defaults['frequency_unit'] = 'month';
        $form->setDefaults($defaults);
        $form->assign('is_freq_priceset', TRUE);

        $priceFieldExtras = civicrm_api3('PricesetIndividualContribution', 'get', array(
          'price_set_id' => $form->get('priceSetId'),
          'sequential'   => TRUE,
          'options'      => [ 'limit' => 0 ]
        ))['values'];

        $priceFieldSettings = [];

        foreach($priceFieldExtras as $extra) {
          $priceFieldSettings[$extra['price_field_value_id']] = [
            'frequencyUnit' => $extra['recurring_contribution_unit'],
            'frequencyInterval' => $extra['recurring_contribution_interval'],
          ];
        }

        CRM_Core_Resources::singleton()->addVars('priceSetFrequency', $priceFieldSettings);

      }
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
  $defaults['recurring_contribution_unit'] = (isset($priceFieldExtras['recurring_contribution_unit'])) ? $priceFieldExtras['recurring_contribution_unit'] : '';
  $defaults['recurring_contribution_interval'] = (isset($priceFieldExtras['recurring_contribution_interval'])) ? $priceFieldExtras['recurring_contribution_interval'] : '';
  $defaults['individual_contribution_source'] = (isset($priceFieldExtras['contribution_source'])) ? $priceFieldExtras['contribution_source'] : '';

  if ($defaults['recurring_contribution_interval'] == 0) {
    $defaults['recurring_contribution_interval'] = '';
  }

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

  if ($recurringInterval != '' && (!CRM_Utils_Type::validate($recurringInterval, 'Int', FALSE, E::ts('Recurring Contribution Interval')) || $recurringInterval < 1)) {
    $errors['recurring_contribution_interval'] = E::ts('Recurring Contribution Interval must be a number greater than 1.');
  }
}

/**
 * Check if membership with auto renew available on the page.
 *
 * @param $form
 * @return bool
 */
function hasAutoRenewMembershipsOnForm($form) {
  $membershipTypes = $form->_membershipTypeValues;
  if (is_array($membershipTypes)) {
    foreach ($membershipTypes as $membershipType) {
      if (isset($membershipType['auto_renew']) && $membershipType['auto_renew']) {
        return TRUE;
      }
    }
  }
  return FALSE;
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

    if ($formName == "CRM_Contribute_Form_Contribution_Main") {
      $elements = $form->_elements;
      $updatedPriceFields = 0;
      $totalPriceFields = 0;
      updatePricefieldElements($elements, $totalPriceFields, $updatedPriceFields, TRUE);

      if ($updatedPriceFields > 0) {
        if (!isset($fields['is_recur']) || !$fields['is_recur']) {
          $errors['is_recur'] = E::ts('To proceed, you need to confirm the recurring contributions can be billed to your credit card');
        }

        if (hasAutoRenewMembershipsOnForm($form) && (!isset($fields['auto_renew']) || !$fields['auto_renew'])) {
          $errors['auto_renew'] = E::ts('To proceed, you need to confirm the membership renewal.');
        }

        $form->setElementError('frequency_interval', NULL);
      }

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
            if ($recurringInterval != '' && (!CRM_Utils_Type::validate($recurringInterval, 'Int', FALSE, E::ts('Recurring Contribution Interval')) || $recurringInterval < 1)) {
              $errors['option_recurring_contribution_interval[' . $i . ']'] = E::ts('Recurring Contribution Interval must be a number greater than 1.');
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
  $individualContributionSource = $form->getSubmitValue('individual_contribution_source');

  if (!$recurringInterval) {
    $recurringInterval = 1;
  }

  if ($recurringUnit == '') {
    $recurringInterval = '';
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
  $individualContribution['recurring_contribution_unit'] = $recurringUnit;
  $individualContribution['recurring_contribution_interval'] = $recurringInterval;
  $individualContribution['contribution_source'] = $individualContributionSource;

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
    if ($formName == 'CRM_Contribute_Form_ContributionPage_Amount') {
      // check if this page use frequency priceset or not
      $submits = $form->getVar('_submitValues');
      if (!$submits['price_set_id']) {
        return;
      }
      $result = civicrm_api3('PriceField', 'get', [
        'sequential' => 1,
        'price_set_id' => $submits['price_set_id'],
        'api.PricesetIndividualContribution.getcount' => ['price_field_id' => "\$value.id"],
      ]);
      $isFrequencyPriceset = FALSE;
      foreach ($result['values'] as $priceField) {
        if ($priceField['api.PricesetIndividualContribution.getcount']) {
          // any of the price field contains frequency settings will work
          $isFrequencyPriceset = TRUE;
          break;
        }
      }
      if ($isFrequencyPriceset) {
        civicrm_api3('ContributionPage', 'create', [
          'id' => $form->getVar('_id'),
          'recur_frequency_unit' => ''
        ]);
      }
    }

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

            $recurringUnit = $form->getSubmitValue('option_recurring_contribution_unit[' . $i . ']');
            $recurringInterval = $form->getSubmitValue('option_recurring_contribution_interval[' . $i . ']');
            $contributionSource = $form->getSubmitValue('option_individual_contribution_source[' . $i . ']');

            if (!$recurringInterval) {
              $recurringInterval = 1;
            }
            if ($recurringUnit == '') {
              $recurringInterval = '';
            }

            if (isset($priceFieldValues[$valueIndex])) {
              $priceFieldValue = $priceFieldValues[$valueIndex];

              civicrm_api3('PricesetIndividualContribution', 'create', array(
                'price_field_id'                  => $priceFieldId,
                'price_field_value_id'            => $priceFieldValue['id'],
                'recurring_contribution_unit'     => $recurringUnit,
                'recurring_contribution_interval' => $recurringInterval,
                'contribution_source'             => $contributionSource,
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
 * Divide the contributions/recurring contributions.
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
      'price_field_id'  => array('!=' => '1'),
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
        if (isset($invidiaulConfig['recurring_contribution_unit']) && $invidiaulConfig['recurring_contribution_unit']) {
          if (!isset($contribution['tax_amount']) || $contribution['tax_amount'] == '') {
            $contribution['tax_amount'] = 0;
          }

          $newContribution = $contribution;

          $lineItemTaxAmount = (isset($lineItem['tax_amount']) && $lineItem['tax_amount'] != '') ? $lineItem['tax_amount'] : 0;

          $newContribution['total_amount'] = $lineItem['line_total'] + $lineItemTaxAmount;
          $newContribution['net_amount'] = $newContribution['total_amount'];
          $newContribution['tax_amount'] = $lineItemTaxAmount;
          if (isset($invidiaulConfig['contribution_source'])) {
            $newContribution['contribution_source'] = $invidiaulConfig['contribution_source'];
          }

          if (number_format(floatval($newContribution['total_amount']), 6) != number_format(floatval($contribution['total_amount']), 6)) {
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

    $recurringAttachedWithMembership = TRUE;
    if ($recurringContribution && !$mainRecurringContributionUpdated) {

      $memberships = civicrm_api3('Membership', 'get', array(
        'contribution_recur_id' => $recurringContribution['id'],
        'sequential'            => 1,
      ));
      $memberships = count($memberships['values']);

      $recurringContributionParams = array(
        'id'                     => $recurringContribution['id'],
        'amount'                 => $recurringContribution['amount'],
      );
      if ($memberships == 0) {
        $recurringAttachedWithMembership = FALSE;
        $recurringContributionParams['contribution_status_id'] = 'Cancelled';
      }

      civicrm_api3('ContributionRecur', 'create', $recurringContributionParams);
    }

    if (!$mainContributionUpdated && $updatedLineItems) {

      $contributionParams = array(
        'total_amount'          => $contribution['total_amount'],
        'tax_amount'            => $contribution['tax_amount'],
        'net_amount'            => $contribution['net_amount'],
        'id'                    => $contribution['id'],
      );

      if (!$recurringAttachedWithMembership) {
        $contributionParams['contribution_recur_id'] = 'null';
      }

      civicrm_api3('Contribution', 'create', $contributionParams);
    }
  }
}

/**
 * Implements hook_civicrm_apiWrappers().
 */
function pricesetfrequency_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['entity'] == 'Contribution' && $apiRequest['action'] == 'completetransaction') {
    $wrappers[] = new CRM_Pricesetfrequency_APIWrappers_ContributionTransactionWrapper();
  }
}
