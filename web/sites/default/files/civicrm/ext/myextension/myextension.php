<?php

require_once 'myextension.civix.php';

use CRM_Myextension_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function myextension_civicrm_config(&$config): void {
  _myextension_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function myextension_civicrm_install(): void {
  _myextension_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function myextension_civicrm_enable(): void {
  _myextension_civix_civicrm_enable();
}
