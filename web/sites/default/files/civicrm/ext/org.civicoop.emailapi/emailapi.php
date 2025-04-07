<?php
require_once 'emailapi.civix.php';
use CRM_Emailapi_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Implements hook_civicrm_container().
 *
 * @param ContainerBuilder $container
 */
function emailapi_civicrm_container(ContainerBuilder $container) {
  if (class_exists('Civi\Emailapi\CompilerPass')) {
    $container->addCompilerPass(new Civi\Emailapi\CompilerPass());
  }
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function emailapi_civicrm_config(&$config) {
  _emailapi_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function emailapi_civicrm_install() {
  _emailapi_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function emailapi_civicrm_enable() {
  _emailapi_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function emailapi_civicrm_managed(&$entities) {

  if (_emailapi_is_civirules_installed()) {
    $select = "SELECT COUNT(*) FROM civirule_action WHERE `name` = 'emailapi_send'";
    $count = CRM_Core_DAO::singleValueQuery($select);
    if ($count == 0) {
      CRM_Core_DAO::executeQuery("INSERT INTO civirule_action (name, label, class_name, is_active) VALUES('emailapi_send', 'Send Email', 'CRM_Emailapi_CivirulesAction_Send', 1);");
    }

    $select = "SELECT COUNT(*) FROM civirule_action WHERE `name` = 'emailapi_send_relationship'";
    $count = CRM_Core_DAO::singleValueQuery($select);
    if ($count == 0) {
      CRM_Core_DAO::executeQuery("INSERT INTO civirule_action (name, label, class_name, is_active) VALUES('emailapi_send_relationship', 'Send Email to a related contact', 'CRM_Emailapi_CivirulesAction_SendToRelatedContact', 1);");
    }
  }
}

function _emailapi_is_civirules_installed() {
  if (civicrm_api3('Extension', 'get', ['key' => 'civirules', 'status' => 'installed'])['count']) {
    return true;
  } elseif (civicrm_api3('Extension', 'get', ['key' => 'org.civicoop.civirules', 'status' => 'installed'])['count']) {
    return true;
  }
  return false;
}
