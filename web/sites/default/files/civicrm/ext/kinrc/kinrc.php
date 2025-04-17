<?php

require_once 'kinrc.civix.php';

use CRM_Kinrc_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function kinrc_civicrm_config(&$config): void {
  _kinrc_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function kinrc_civicrm_install(): void {
  _kinrc_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function kinrc_civicrm_enable(): void {
  _kinrc_civix_civicrm_enable();
}


