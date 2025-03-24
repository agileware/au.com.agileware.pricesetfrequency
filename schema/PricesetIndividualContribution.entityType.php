<?php
use CRM_Pricesetfrequency_ExtensionUtil as E;

return [
  'name' => 'PricesetIndividualContribution',
  'table' => 'civicrm_priceset_individual_contribution',
  'class' => 'CRM_Pricesetfrequency_DAO_PricesetIndividualContribution',
  'getInfo' => fn() => [
    'title' => E::ts('Priceset Individual Contribution'),
    'title_plural' => E::ts('Priceset Individual Contributions'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique PricesetIndividualContribution ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'price_field_id' => [
      'title' => E::ts('Price Field ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to PriceField'),
      'entity_reference' => [
        'entity' => 'PriceField',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'price_field_value_id' => [
      'title' => E::ts('Price Field Value ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to PriceFieldValue'),
      'entity_reference' => [
        'entity' => 'PriceFieldValue',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'recurring_contribution_unit' => [
      'title' => E::ts('Recurring Contribution Unit'),
      'sql_type' => 'varchar(8)',
      'input_type' => 'Text',
      'description' => E::ts('Time units for recurrence of payment.'),
      'default' => 'month',
    ],
    'contribution_source' => [
      'title' => E::ts('Contribution Source'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Source of individual contribution.'),
      'default' => NULL,
      'usage' => [
        'import',
        'export',
        'duplicate_matching',
      ],
    ],
    'recurring_contribution_interval' => [
      'title' => E::ts('Recurring Contribution Interval'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Number of time units for recurrence of payment.'),
    ],
  ],
];
