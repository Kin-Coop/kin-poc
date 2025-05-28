<?php
/**
 * CiviCRM Configuration Loader (org.civicoop.configitems)
 * For information about this extension, see README.md and info.xml.
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @author Johan Vervloet (Chirojeugd-Vlaanderen vzw) <helpdesk@chiro.be>
 * @author Kevin Levie <kevin.levie@civicoop.org>
 *
 * @package org.civicoop.configitems
 * @license AGPL-3.0
 * @link https://github.com/civicoop/org.civicoop.configitems
 */

require_once 'civiconfig.civix.php';
use CRM_Civiconfig_ExtensionUtil as E;

use \Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @return \Civi\ConfigItems\Entity\Factory
 */
function civiconfig_get_entity_factory() {
  $container = \Civi::container();
  if ($container->has('civiconfig_entity_factory')) {
    return \Civi::service('civiconfig_entity_factory');
  }
  return null;
}

/**
 * @return \Civi\ConfigItems\FileFormat\Factory
 */
function civiconfig_get_fileformat_factory() {
  $container = \Civi::container();
  if ($container->has('civiconfig_fileformat_factory')) {
    return \Civi::service('civiconfig_fileformat_factory');
  }
  return null;
}

/**
 * @return \Civi\ConfigItems\QueueService
 */
function civiconfig_get_queue_service() {
  $container = \Civi::container();
  if ($container->has('civiconfig_queue_service')) {
    return \Civi::service('civiconfig_queue_service');
  }
  return null;
}

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function civiconfig_civicrm_container(ContainerBuilder $container) {
  $container->addCompilerPass(new Civi\ConfigItems\CompilerPass(), \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, 99);
  Civi\ConfigItems\FileFormat\EventListener::addEventListeners($container);
}

/**
 * @return string
 */
function civiconfig_get_import_directory() {
  $config = \CRM_Core_Config::singleton();
  $directoryName = $config->customFileUploadDir . 'ConfigItemSets';
  if (!file_exists($directoryName)) {
    \CRM_Utils_File::createDir($directoryName);
  }
  return $directoryName . DIRECTORY_SEPARATOR;
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civiconfig_civicrm_config(&$config) {
  _civiconfig_civix_civicrm_config($config);
}
/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civiconfig_civicrm_install() {
  _civiconfig_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civiconfig_civicrm_enable() {
  _civiconfig_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civiconfig_civicrm_caseTypes(&$caseTypes) {
  _civiconfig_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Add navigation for Postcode Lookup under "Administer" menu
 */
function civiconfig_civicrm_navigationMenu(&$menu) {
  $item[] =  [
    'label'      => E::ts('Manage configuration sets'),
    'name'       => 'config_import_export',
    'url'        => 'civicrm/admin/civiconfig?reset=1',
    'permission' => 'administer CiviCRM',
    'operator'   => NULL,
    'separator'  => FALSE,
    'active'     => 1
  ];
  _civiconfig_civix_insert_navigation_menu($menu, 'Administer/System Settings', $item[0]);
  _civiconfig_civix_navigationMenu($menu);
}
