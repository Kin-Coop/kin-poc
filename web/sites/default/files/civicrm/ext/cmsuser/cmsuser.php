<?php

require_once 'cmsuser.civix.php';
// phpcs:disable
use CRM_Cmsuser_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function cmsuser_civicrm_config(&$config) {
  _cmsuser_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function cmsuser_civicrm_install() {
  _cmsuser_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function cmsuser_civicrm_enable() {
  _cmsuser_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function cmsuser_civicrm_managed(&$entities) {
  // Load the triggers when civirules is installed.
  if (_cmsuser_is_civirules_installed()) {
    CRM_Civirules_Utils_Upgrader::insertConditionsFromJson(E::path('civirules/conditions.json'));
    CRM_Civirules_Utils_Upgrader::insertActionsFromJson(E::path('civirules/actions.json'));
  }
}

/**
 * Function to check whether civirules is installed.
 *
 * @return bool
 */
function _cmsuser_is_civirules_installed() {
  if (civicrm_api3('Extension', 'get', ['key' => 'civirules', 'status' => 'installed'])['count']) {
    return TRUE;
  } elseif (civicrm_api3('Extension', 'get', ['key' => 'org.civicoop.civirules', 'status' => 'installed'])['count']) {
    return TRUE;
  }
  return FALSE;
}

function cmsuser_civicrm_searchTasks($objectName, &$tasks) {
  if ($objectName == 'contact') {
    if (CRM_Core_Permission::check('administer CiviCRM data') || CRM_Core_Permission::check('administer CiviCRM')) {
      $tasks[] = [
        'title' => 'Create CMS user',
        'class' => 'CRM_Cmsuser_Form_Task_CreateCMSUser',
        'url' => 'civicrm/task/create-cmsuser',
        'icon' => 'fa-user-plus'
      ];
    }
  }
}
