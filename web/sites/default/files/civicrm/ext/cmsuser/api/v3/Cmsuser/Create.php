<?php

use CRM_Cmsuser_ExtensionUtil as E;

/**
 * Cmsuser.Create API specification (optional)
 *
 * @param array $params description of fields supported by this API call
 */
function _civicrm_api3_cmsuser_create_spec(&$params) {
  $params['cms_name'] = [
    'title' => E::ts('CMS Username'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['email'] = [
    'title' => E::ts('Email Address'),
    'type' => CRM_Utils_Type::T_EMAIL,
  ];
  $params['cms_pass'] = [
    'title' => E::ts('CMS Password'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['contact_id'] = [
    'title' => E::ts('Contact ID'),
    'description' => E::ts('CiviCRM contact ID'),
    'api.required' => TRUE,
    'type' => CRM_Utils_Type::T_INT,
  ];
  $params['notify'] = [
    'title' => E::ts('Notify user'),
    'description' => E::ts('Notify user of new CMS account'),
    'api.required' => FALSE,
    'api.default' => FALSE,
    'type' => CRM_Utils_Type::T_BOOLEAN,
  ];
}

/**
 * Cmsuser.Create API
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_cmsuser_create($params) {
  // Check for existing contact
  $ufMatchByContactID = \Civi\Api4\UFMatch::get(FALSE)
    ->addWhere('contact_id', '=', $params['contact_id'])
    ->execute()
    ->first();

  if (!empty($ufMatchByContactID)) {
    return civicrm_api3_create_success(['uf_id' => $ufMatchByContactID['id'], 'created' => FALSE], $params);
  }

  // Get email (either from param or from contact record.
  if (empty($params['email'])) {
    $email = \Civi\Api4\Email::get(FALSE)
      ->addSelect('email')
      ->addWhere('is_primary', '=', TRUE)
      ->addWhere('contact_id', '=', $params['contact_id'])
      ->execute()
      ->first();
    if (empty($email['email'])) {
      throw new CRM_Core_Exception('Email is required and the contact has no email address.');
    }
    $params['email'] = $email['email'];
  }

  // If no cms_name (username) specified use email
  if (empty($params['cms_name'])) {
    $params['cms_name'] = $params['email'];
  }

  $ufMatchByName = \Civi\Api4\UFMatch::get(FALSE)
    ->addWhere('uf_name', '=', $params['cms_name'])
    ->execute()
    ->first();
  if (!empty($ufMatchByName)) {
    throw new CRM_Core_Exception("Cannot create CMS user for contact ID: {$params['contact_id']}. CMS Username: {$params['cms_name']} already exists.");
  }

  $cmsUserParams = [
    'email' => $params['email'],
    'cms_name' => $params['cms_name'],
    'contactID' => $params['contact_id'],
    'notify' => $params['notify'],
  ];

  // If no password specified generate a random one
  // For WordPress we defer to the CMS function via CRM_Utils_System_WordPress::createUser
  if (empty($params['cms_pass']) && !function_exists('wp_generate_password')) {
    $params['cms_pass'] = bin2hex(random_bytes(10));
    $cmsUserParams['cms_pass'] = $params['cms_pass'];
  }

  $ufID = CRM_Core_BAO_CMSUser::create($cmsUserParams, 'email');
  if (!$ufID) {
    throw new CRM_Core_Exception('Failed to create CMS user account');
  }

  return civicrm_api3_create_success(['uf_id' => $ufID, 'created' => TRUE], $params);
}
