<?php

use CRM_Kinpayments_ExtensionUtil as E;

return [
  'name' => 'KinpaymentsPayment',
  'table' => 'civicrm_kinpayments_payment',
  'class' => 'CRM_Kinpayments_DAO_KinpaymentsPayment',
  'getInfo' => fn() => [
    'title' => E::ts('Kin Payment'),
    'title_plural' => E::ts('Kin Payments'),
    'log' => TRUE,
    'label_field' => 'id',
  ],
  'getIndices' => fn() => [
    'index_transaction_date_time' => [
      'fields' => [
        'datetime' => TRUE,
      ],
    ],
    'contribution_id' => [
      'fields' => [
        'contribution_id' => TRUE,
      ],
      'unique' => TRUE,
    ],
    'contact_id' => [
      'fields' => [
        'contact_id' => TRUE,
      ],
    ],
    'index_status_id' => [
      'fields' => [
        'payment_status_id' => TRUE,
      ],
    ],
    'match_score' => [
      'fields' => [
        'match_score' => TRUE,
      ]
    ],
    'bank_reference' => [
      'fields' => [
        'bank_reference' => TRUE,
      ],
    ],
    'customer_reference' => [
      'fields' => [
        'customer_reference' => TRUE,
      ],
    ],
    'customer_account_number' => [
      'fields' => [
        'customer_account_number' => TRUE,
      ],
    ],
    'unique_bank_payment' => [
      'fields' => [
        'amount' => TRUE,
        'datetime' => TRUE,
        'customer_account_number' => TRUE,
        'bank_reference' => TRUE,
      ],
      'unique' => TRUE,
    ],
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
    'amount' => [
      'title' => E::ts('Payment Amount'),
      'sql_type' => 'decimal(20,2)',
      'input_type' => 'Text',
      'required' => TRUE,
      'default' => 0,
      'description' => E::ts('Payment amount'),
    ],
    'datetime' => [
      'title' => E::ts('Payment Date'),
      'sql_type' => 'datetime',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'description' => E::ts('Date and time of the payment'),
      'default' => 'CURRENT_TIMESTAMP',
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'customer_reference' => [
      'title' => E::ts('Customer Reference'),
      'sql_type' => 'varchar(256)',
      'input_type' => 'Text',
      'default' => 'ABC',
      'description' => E::ts('The member name'),
    ],
    'bank_reference' => [
      'title' => E::ts('Bank Reference'),
      'sql_type' => 'varchar(256)',
      'input_type' => 'Text',
      'default' => '123',
      'description' => E::ts('The unique reference'),
    ],
    'customer_account_number' => [
      'title' => E::ts('Customer Account Number'),
      'sql_type' => 'varchar(16)',
      'input_type' => 'Text',
      'description' => E::ts('The account number of the member'),
    ],
    'payment_status_id' => [
      'title' => E::ts('Kin Payment status'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'required' => TRUE,
      'description' => E::ts('FK to payment status option value'),
      'default' => 1,
      'pseudoconstant' => [
        'option_group_name' => 'kin_payment_status',
      ],
    ],
    'contribution_id' => [
      'title' => E::ts('Contribution'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'description' => E::ts('FK to contribution ID'),
      'entity_reference' => [
        'entity' => 'Contribution',
        'key' => 'id',
        'on_delete' => 'RESTRICT',
      ],
    ],
    'match_score' => [
      'title' => E::ts('Match Score'),
      'sql_type' => 'int',
      'input_type' => 'Number',
      'description' => E::ts('Matching confidence score 0-100. 0 = no match, 100 = certainty.'),
      'default' => 0,
      'input_attrs' => [
        'min' => 0,
        'max' => 100,
      ],
    ],
    'notes' => [
      'title' => E::ts('Notes'),
      'sql_type' => 'longtext',
      'input_type' => 'Textarea',
      'unique_name' => 'kin_payment_notes',
      'description' => E::ts('User generated notes about the payment'),
      'input_attrs' => [
        'rows' => 4,
        'cols' => 60,
      ],
    ],
  ],
  'getPaths' => fn() => [],
];
