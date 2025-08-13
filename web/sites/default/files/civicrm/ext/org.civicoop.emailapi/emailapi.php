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
 * @param string $op the type of operation being performed; 'check' or 'enqueue'
 * @param \CRM_Queue_Queue|NULL $queue (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return void
 *   For 'check' operations, return array(bool) (TRUE if an upgrade is required)
 *   For 'enqueue' operations, return void
 */
function emailapi_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  if ($op === 'enqueue') {
    $task = new CRM_Queue_Task(
      ['CRM_Emailapi_Upgrader', 'postUpgrade'],
      [],
      'Update EmailAPI Actions'
    );
    return $queue->createItem($task);
  }
}
