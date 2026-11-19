<?php

require_once 'searchkitmaps.civix.php';
// phpcs:disable
use CRM_Searchkitmaps_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function searchkitmaps_civicrm_config(&$config): void {
  _searchkitmaps_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function searchkitmaps_civicrm_install(): void {
  _searchkitmaps_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function searchkitmaps_civicrm_enable(): void {
  _searchkitmaps_civix_civicrm_enable();
}
