<?php

require_once 'kin_config.civix.php';

use CRM_KinConfig_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function kin_config_civicrm_config(&$config): void {
  _kin_config_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function kin_config_civicrm_install(): void {
  _kin_config_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function kin_config_civicrm_enable(): void {
  _kin_config_civix_civicrm_enable();
}
