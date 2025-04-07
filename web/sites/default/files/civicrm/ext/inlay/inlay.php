<?php

require_once 'inlay.civix.php';
// phpcs:disable
use CRM_Inlay_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function inlay_civicrm_config(&$config) {
  _inlay_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_permission.
 *
 * @param string $permissions
 */
function inlay_civicrm_permission(&$permissions) {
  $prefix = E::ts('Inlay') . ': ';
  $permissions += [
    'administer Inlays' => [
      'label' => $prefix . E::ts('Administer Inlays'),
      'description' => E::ts('Create, delete, edit any type of inlay'),
    ],
  ];
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function inlay_civicrm_navigationMenu(&$menu) {
  // Could not get 'Customise Data and Screens'
  _inlay_civix_insert_navigation_menu($menu, 'Administer', array(
    'label' => E::ts('Inlays'),
    'name' => 'inlays',
    'url' => 'civicrm/a#/inlays',
    'permission' => 'administer Inlays',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _inlay_civix_navigationMenu($menu);
}
