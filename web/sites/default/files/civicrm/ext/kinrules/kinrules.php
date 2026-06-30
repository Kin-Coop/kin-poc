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
 * Adds an "Export CiviRules to CSV" item under Administer > CiviRules.
 * Paste this function into kinrules.php (the file civix generated).
 */
function kinrules_civicrm_navigationMenu(&$menu) {
  // Try to nest under the existing CiviRules menu; fall back to Administer.
  $parentName = 'CiviRules';
  $parentId = _kinrules_find_menu_id($menu, $parentName);
  if (!$parentId) {
    $parentName = 'Administer';
    $parentId = _kinrules_find_menu_id($menu, $parentName);
  }

  _civirules_insert_navigation_menu($menu, $parentName, [
    'label'      => E::ts('Export CiviRules to CSV'),
    'name'       => 'kinrules_export',
    'url'        => 'civicrm/kinrules/export',
    'permission' => 'administer CiviCRM',
    'operator'   => 'OR',
    'separator'  => 0,
  ]);
  _kinrules_navMenu_flush();
}

/**
 * Helper: insert a menu item under a named parent (mirrors core helper).
 */
function _civirules_insert_navigation_menu(&$menu, $path, $item) {
  if (empty($path)) {
    $menu[] = [
      'attributes' => array_merge([
        'label'      => $item['label'] ?? NULL,
        'active'     => 1,
      ], $item),
    ];
    return TRUE;
  }
  foreach ($menu as &$entry) {
    if ($entry['attributes']['name'] == $path) {
      if (!isset($entry['child'])) {
        $entry['child'] = [];
      }
      $entry['child'][] = [
        'attributes' => array_merge([
          'label'  => $item['label'] ?? NULL,
          'active' => 1,
        ], $item),
      ];
      return TRUE;
    }
    if (!empty($entry['child']) && _civirules_insert_navigation_menu($entry['child'], $path, $item)) {
      return TRUE;
    }
  }
  return FALSE;
}

/**
 * Helper: find a menu item's name by its label or name.
 */
function _kinrules_find_menu_id($menu, $name) {
  foreach ($menu as $entry) {
    if (($entry['attributes']['name'] ?? '') === $name) {
      return $entry['attributes']['navID'] ?? TRUE;
    }
    if (!empty($entry['child'])) {
      $found = _kinrules_find_menu_id($entry['child'], $name);
      if ($found) {
        return $found;
      }
    }
  }
  return NULL;
}

/**
 * Helper: flush the navigation cache so the new item appears.
 */
function _kinrules_navMenu_flush() {
  CRM_Core_BAO_Navigation::resetNavigation();
}
