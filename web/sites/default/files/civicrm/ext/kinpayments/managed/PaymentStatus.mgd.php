<?php

use CRM_Kinpayments_ExtensionUtil as E;

return [

  // Option Group
  [
    'name' => 'KinPaymentStatus',
    'entity' => 'OptionGroup',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'kin_payment_status',
        'title' => E::ts('Kin Payment Status'),
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'option_value_fields' => [
          'name',
          'label',
          'description',
        ],
      ],
      'match' => ['name'],
    ],
  ],

  // Pending
  [
    'name' => 'KinPaymentStatus:Pending',
    'entity' => 'OptionValue',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'kin_payment_status',
        'label' => E::ts('Pending'),
        'description' => E::ts('Pending'),
        'value' => 1,
        'name' => 'pending',
        'weight' => 1,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'domain_id' => NULL,
      ],
      'match' => ['option_group_id', 'name'],
    ],
  ],

  // Not found
  [
    'name' => 'KinPaymentStatus:NotFound',
    'entity' => 'OptionValue',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'kin_payment_status',
        'label' => E::ts('Not found'),
        'description' => E::ts('Not found'),
        'value' => 2,
        'name' => 'not_found',
        'weight' => 2,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'domain_id' => NULL,
      ],
      'match' => ['option_group_id', 'name'],
    ],
  ],

  // Matched
  [
    'name' => 'KinPaymentStatus:Matched',
    'entity' => 'OptionValue',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'kin_payment_status',
        'label' => E::ts('Matched'),
        'description' => E::ts('Matched'),
        'value' => 3,
        'name' => 'matched',
        'weight' => 3,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'domain_id' => NULL,
      ],
      'match' => ['option_group_id', 'name'],
    ],
  ],
];
