<?php

function _civicrm_api3_kinpayments_payment_get_spec(&$spec) {}

function civicrm_api3_kinpayments_payment_get($params) {

  $where = [];

  foreach ($params as $key => $value) {
    if (in_array($key, ['version', 'sequential', 'check_permissions'])) {
      continue;
    }
    $where[] = [$key, '=', $value];
  }

  $result = civicrm_api4('KinpaymentsPayment', 'get', [
    'where' => $where,
    'checkPermissions' => FALSE,
  ]);

  return civicrm_api3_create_success(
    $result->getArrayCopy(),
    $params,
    'KinpaymentsPayment',
    'get'
  );
}

function civicrm_api3_kinpayments_payment_create($params) {

  unset(
    $params['version'],
    $params['check_permissions'],
    $params['sequential']
  );

  $result = civicrm_api4('KinpaymentsPayment', 'save', [
    'records' => [$params],
    'checkPermissions' => FALSE,
  ]);

  return civicrm_api3_create_success(
    $result->getArrayCopy(),
    $params,
    'KinpaymentsPayment',
    'create'
  );
}

function civicrm_api3_kinpayments_payment_delete($params) {

  if (empty($params['id'])) {
    throw new API_Exception('Missing id');
  }

  civicrm_api4('KinpaymentsPayment', 'delete', [
    'where' => [
      ['id', '=', $params['id']],
    ],
  ]);

  return civicrm_api3_create_success([], $params);
}

/*
function civicrm_api3_kinpayments_payment_getfields($params) {

  $fields = [

    'id' => [
      'name' => 'id',
      'title' => ts('ID'),
      'type' => CRM_Utils_Type::T_INT,
    ],

    'contact_id' => [
      'name' => 'contact_id',
      'title' => ts('Contact'),
      'type' => CRM_Utils_Type::T_INT,
      'FKApiName' => 'Contact',
    ],

    'amount' => [
      'name' => 'amount',
      'title' => ts('Payment Amount'),
      'type' => CRM_Utils_Type::T_MONEY,
      'api.required' => 1,
    ],

    'datetime' => [
      'name' => 'datetime',
      'title' => ts('Payment Date'),
      'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
      'api.required' => 1,
    ],

    'customer_reference' => [
      'name' => 'customer_reference',
      'title' => ts('Customer Reference'),
      'type' => CRM_Utils_Type::T_STRING,
    ],

    'bank_reference' => [
      'name' => 'bank_reference',
      'title' => ts('Bank Reference'),
      'type' => CRM_Utils_Type::T_STRING,
    ],

    'customer_account_number' => [
      'name' => 'customer_account_number',
      'title' => ts('Customer Account Number'),
      'type' => CRM_Utils_Type::T_STRING,
    ],

    'payment_status_id' => [
      'name' => 'payment_status_id',
      'title' => ts('Payment Status'),
      'type' => CRM_Utils_Type::T_INT,
      'api.required' => 1,
      'pseudoconstant' => [
        'optionGroupName' => 'kin_payment_status',
      ],
    ],

    'contribution_id' => [
      'name' => 'contribution_id',
      'title' => ts('Contribution'),
      'type' => CRM_Utils_Type::T_INT,
      'FKApiName' => 'Contribution',
    ],

  ];

  return civicrm_api3_create_success($fields, $params);
}

function civicrm_api3_kinpayments_payment_getfields($params) {

  $entity = \Civi\Api4\KinpaymentsPayment::getFields(FALSE)
    ->execute();

  $fields = [];

  foreach ($entity as $field) {
    $fields[$field['name']] = [
      'title' => $field['title'] ?? $field['name'],
      'type' => $field['data_type'] ?? 'String',
    ];
  }

  return civicrm_api3_create_success($fields, $params);
}
*/

function civicrm_api3_kinpayments_payment_getfields($params) {

  $fields = [];

  $api4Fields = \Civi\Api4\KinpaymentsPayment::getFields(FALSE)
    ->execute();

  foreach ($api4Fields as $field) {

    $fields[$field['name']] = [
      'name' => $field['name'],
      'title' => $field['title'] ?? $field['name'],
      'data_type' => $field['data_type'] ?? 'String',
    ];

    /*
    if (!empty($field['required'])) {
      $fields[$field['name']]['api.required'] = 1;
    }
    */
  }

  return civicrm_api3_create_success($fields, $params);
}

function civicrm_api3_kinpayments_payment_getunique($params) {

  return civicrm_api3_create_success([
    ['id']
  ], $params);
}


/**
 * APIv3 wrapper for KinpaymentsPayment.match_payments.
 *
 * This lets the action be registered as a CiviCRM Scheduled Job so it can
 * be triggered automatically after a CSV import or on a cron schedule.
 *
 * Scheduled Job settings (Administer → System Settings → Scheduled Jobs):
 *   API entity  : KinpaymentsPayment
 *   API action  : match_payments
 *   Parameters  : include_unmatched=0   (set to 1 to reprocess unmatched)
 */

/**
 * KinpaymentsPayment.match_payments API spec.
 *
 * @param array $spec
 */
function _civicrm_api3_kinpayments_payment_match_payments_spec(array &$spec): void
{
  $spec['include_unmatched'] = [
    'title' => 'Include Unmatched',
    'description' => 'Set to 1 to also reprocess records with status Not Matched (2).',
    'type' => \CRM_Utils_Type::T_BOOLEAN,
    'api.default' => 0,
  ];
  $spec['dry_run'] = [
    'title' => 'Dry Run',
    'description' => 'Set to 1 to score matches without writing to the database.',
    'type' => \CRM_Utils_Type::T_BOOLEAN,
    'api.default' => 0,
  ];
}

/**
 * KinpaymentsPayment.match_payments API action.
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_kinpayments_payment_match_payments(array $params): array
{
  $summary = \Civi\Api4\KinpaymentsPayment::matchPayments(FALSE)
    ->setIncludeUnmatched(!empty($params['include_unmatched']))
    ->setDryRun(!empty($params['dry_run']))
    ->execute()
    ->first();

  return civicrm_api3_create_success($summary, $params, 'KinpaymentsPayment', 'match_payments');
}
