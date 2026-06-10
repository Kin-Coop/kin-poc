<?php
  use CRM_Grant_ExtensionUtil as E;

  // This enables custom fields for Grant entities
  return [
    [
      'name' => 'cg_extend_objects:Grant',
      'entity' => 'OptionValue',
      'cleanup' => 'always',
      'update' => 'always',
      'params' => [
        'version' => 4,
        'values' => [
          'option_group_id.name' => 'cg_extend_objects',
          'label' => E::ts('Kin Payment'),
          'value' => 'KinpaymentsPayment',
          'name' => 'civicrm_kinpayments_payment',
          'is_active' => TRUE,
        ],
        'match' => ['name'],
      ],
    ],
  ];
