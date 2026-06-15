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

  $api4Fields = \Civi\Api4\KinpaymentsPayment::getFields()
    ->execute();

  foreach ($api4Fields as $field) {

    $fields[$field['name']] = [
      'name' => $field['name'],
      'title' => $field['title'] ?? $field['name'],
      'data_type' => $field['data_type'] ?? 'String',
    ];

    if (!empty($field['required'])) {
      $fields[$field['name']]['api.required'] = 1;
    }
  }

  return civicrm_api3_create_success($fields, $params);
}

function civicrm_api3_kinpayments_payment_getunique($params) {

  return civicrm_api3_create_success([
    ['id']
  ], $params);
}
