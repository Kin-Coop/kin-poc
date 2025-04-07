<?php

use CRM_Cmsuser_ExtensionUtil as E;

/**
 * Cmsuser.Get API specification (optional)
 *
 * @param array $params description of fields supported by this API call
 */
function _civicrm_api3_cmsuser_get_spec(&$params) {
  $params['contact_id'] = [
    'title' => 'Contact ID',
    'description' => 'CiviCRM contact ID',
    'api.required' => TRUE,
    'type' => CRM_Utils_Type::T_INT,
  ];
}

/**
 * Cmsuser.Get API
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_cmsuser_get($params) {
  $result = [];
  try {
    $user = CRM_Core_Config::singleton()->userSystem->getUser($params['contact_id']);
    if ($user) {
      $result[$params['sequential'] ? 0 : $params['contact_id']] = [
        'uf_id' => $user['id'],
        'uf_name' => $user['name'],
        'contact_id' => $params['contact_id'],
      ];
    }
  }
  catch (Exception $e) {
    // no need for Exception if it just means no user found
    if ($e->getMessage() != "Expected one UFMatch but found 0") {
      throw $e;
    }
  }
  return civicrm_api3_create_success($result);
}
