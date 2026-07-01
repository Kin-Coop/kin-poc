<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'kinrules.civix.php';
// phpcs:enable

use CRM_Kinrules_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function kinrules_civicrm_config(\CRM_Core_Config $config): void {
  _kinrules_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function kinrules_civicrm_install(): void {
  _kinrules_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function kinrules_civicrm_enable(): void {
  _kinrules_civix_civicrm_enable();
}


/**
 * Implements hook_civicrm_navigationMenu().
 *
 * Adds an "Export CiviRules to CSV" item. Paste this into kinrules.php,
 * REPLACING the previous nav snippet entirely. Make sure
 *   use CRM_Kinrules_ExtensionUtil as E;
 * is present near the top of kinrules.php (civix adds it by default).
 *
 * This version uses CiviCRM's core _civicrm_insert_navigation_menu()
 * helper rather than a hand-rolled one, so there is no risk of
 * redeclaring a function that CiviRules already defines.
 */

function kinrules_civicrm_navigationMenu(&$menu)
{
  _kinrules_add_nav_item($menu, 'Administer', [
    'label' => E::ts('Export CiviRules to CSV'),
    'name' => 'kinrules_export',
    'url' => 'civicrm/kinrules/export',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ]);
  _kinrules_nav_menu_flush();
}

/**
 * Insert under a top-level parent by its menu "name", falling back to
 * top level if the parent is not found. Uses the core helper.
 */
function _kinrules_add_nav_item(&$menu, $parentName, $item)
{
  // CiviCRM core provides this helper; it walks the tree and inserts.
  if (function_exists('_civicrm_insert_navigation_menu')) {
    _civicrm_insert_navigation_menu($menu, $parentName, $item);
  } else {
    // Extremely defensive fallback: append at top level.
    $menu[] = ['attributes' => $item + ['active' => 1]];
  }
}

/**
 * Flush navigation cache so the item appears.
 */
function _kinrules_nav_menu_flush()
{
  if (is_callable(['CRM_Core_BAO_Navigation', 'resetNavigation'])) {
    CRM_Core_BAO_Navigation::resetNavigation();
  }
}
