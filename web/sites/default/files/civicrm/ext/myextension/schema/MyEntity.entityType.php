<?php
use CRM_Myextension_ExtensionUtil as E;

return [
  'name' => 'MyEntity',
  'table' => 'civicrm_my_entity',
  'class' => 'CRM_Myextension_DAO_MyEntity',
  'getInfo' => fn() => [
    'title' => E::ts('MyEntity'),
    'title_plural' => E::ts('MyEntities'),
    'description' => E::ts('FIXME'),
    'log' => TRUE,
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique MyEntity ID'),
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
