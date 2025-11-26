<?php

require_once 'egextension.civix.php';

use CRM_Egextension_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function egextension_civicrm_config(&$config): void {
  _egextension_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function egextension_civicrm_install(): void {
  _egextension_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function egextension_civicrm_enable(): void {
  _egextension_civix_civicrm_enable();
}
