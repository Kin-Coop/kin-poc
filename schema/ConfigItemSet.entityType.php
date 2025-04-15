<?php
use CRM_Civiconfig_ExtensionUtil as E;
return [
  'name' => 'ConfigItemSet',
  'table' => 'civicrm_config_item_set',
  'class' => 'CRM_Civiconfig_DAO_ConfigItemSet',
  'getInfo' => fn() => [
    'title' => E::ts('Config Item Set'),
    'title_plural' => E::ts('Config Item Sets'),
    'description' => E::ts('FIXME'),
    'log' => FALSE,
    'label_field' => 'title',
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique ConfigItemSet ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'name' => [
      'title' => E::ts('Name'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'title' => [
      'title' => E::ts('Title'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
    ],
    'description' => [
      'title' => E::ts('Description'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
    ],
    'version' => [
      'title' => E::ts('Version'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Text',
      'required' => TRUE,
      'default' => 1,
    ],
    'version_hash' => [
      'title' => E::ts('Version Hash'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('The version hash is used to determine the current version in use. The version hash will be set when a new export is downloaded.'),
    ],
    'entities' => [
      'title' => E::ts('Entities'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
    'configuration' => [
      'title' => E::ts('Configuration'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
    'import_file_format' => [
      'title' => E::ts('Import File Format'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'import_sub_directory' => [
      'title' => E::ts('Import Sub Directory'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
    ],
    'import_configuration' => [
      'title' => E::ts('Import Configuration'),
      'sql_type' => 'text',
      'input_type' => 'TextArea',
      'serialize' => constant('CRM_Core_DAO::SERIALIZE_JSON'),
    ],
  ],
];
