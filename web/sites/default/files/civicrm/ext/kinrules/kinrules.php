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
 * Adds an "Export CiviRules to CSV" item under the Administer menu.

 * This version manipulates the $menu array directly with no calls to any
 * CiviCRM core helper functions, so it cannot collide with another
 * extension's function and cannot fatal on a version-specific helper
 * signature. It walks the tree, finds the Administer parent by name, and
 * appends a child.
 */
function kinrules_civicrm_navigationMenu(&$menu)
{
  // Find the highest existing navID so we can assign a new unique one.
  $maxId = _kinrules_nav_max_id($menu);
  $newId = $maxId + 1;

  $item = [
    'attributes' => [
      'label' => E::ts('Export CiviRules to CSV'),
      'name' => 'kinrules_export',
      'url' => 'civicrm/kinrules/export',
      'permission' => 'administer CiviCRM',
      'operator' => 'OR',
      'separator' => 0,
      'active' => 1,
      'navID' => $newId,
    ],
    'child' => NULL,
  ];

  // Try to nest under "Administer". If not found, append at top level.
  if (!_kinrules_nav_append_under($menu, 'Administer', $item)) {
    $item['attributes']['parentID'] = NULL;
    $menu[$newId] = $item;
  }
}

/**
 * Recursively find the maximum navID currently in the menu tree.
 */
function _kinrules_nav_max_id($menu)
{
  $max = 0;
  foreach ($menu as $key => $entry) {
    if (is_numeric($key) && (int)$key > $max) {
      $max = (int)$key;
    }
    if (!empty($entry['attributes']['navID']) && (int)$entry['attributes']['navID'] > $max) {
      $max = (int)$entry['attributes']['navID'];
    }
    if (!empty($entry['child']) && is_array($entry['child'])) {
      $childMax = _kinrules_nav_max_id($entry['child']);
      if ($childMax > $max) {
        $max = $childMax;
      }
    }
  }
  return $max;
}

/**
 * Recursively find the parent whose attributes['name'] matches $parentName
 * and append $item as a child. Returns TRUE if inserted.
 */
function _kinrules_nav_append_under(&$menu, $parentName, $item)
{
  foreach ($menu as &$entry) {
    if (isset($entry['attributes']['name']) && $entry['attributes']['name'] === $parentName) {
      if (empty($entry['child']) || !is_array($entry['child'])) {
        $entry['child'] = [];
      }
      $item['attributes']['parentID'] = $entry['attributes']['navID'] ?? NULL;
      $entry['child'][$item['attributes']['navID']] = $item;
      return TRUE;
    }
    if (!empty($entry['child']) && is_array($entry['child'])) {
      if (_kinrules_nav_append_under($entry['child'], $parentName, $item)) {
        return TRUE;
      }
    }
  }
  return FALSE;
}
