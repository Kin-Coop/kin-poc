<?php
use CRM_Kinpayments_ExtensionUtil as E;

return [
  'name' => 'KinpaymentsPayment',
  'table' => 'civicrm_kinpayments_payment',
  'class' => 'CRM_Kinpayments_DAO_KinpaymentsPayment',
  'getInfo' => fn() => [
    'title' => E::ts('KinpaymentsPayment'),
    'title_plural' => E::ts('KinpaymentsPayments'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique KinpaymentsPayment ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'contact_id' => [
      'title' => E::ts('Contact ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to Contact'),
      'entity_reference' => [
        'entity' => 'Contact',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
  ],
  'getIndices' => fn() => [],
  'getPaths' => fn() => [],
];
