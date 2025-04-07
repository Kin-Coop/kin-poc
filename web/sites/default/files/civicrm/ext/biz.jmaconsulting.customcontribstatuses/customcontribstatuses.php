<?php

require_once 'customcontribstatuses.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function customcontribstatuses_civicrm_config(&$config) {
  _customcontribstatuses_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function customcontribstatuses_civicrm_xmlMenu(&$files) {
}

/**
 * Implementation of hook_civicrm_install
 */
function customcontribstatuses_civicrm_install() {
  return _customcontribstatuses_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function customcontribstatuses_civicrm_uninstall() {
  return;
}

/**
 * Implementation of hook_civicrm_enable
 */
function customcontribstatuses_civicrm_enable() {
  return _customcontribstatuses_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 */
function customcontribstatuses_civicrm_disable() {
  return;
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function customcontribstatuses_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return;
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function customcontribstatuses_civicrm_managed(&$entities) {
  return;
}

function customcontribstatuses_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Contribute_Form_Contribution' && $form->getVar('_action') == CRM_Core_Action::UPDATE 
    && $form->_defaultValues['contribution_status_id'] != $fields['contribution_status_id']) {
    $contributionStatuses = CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name');
    $errorMsg =  ts("Cannot change contribution status from %1 to %2.", array(1 => $contributionStatuses[$form->_defaultValues['contribution_status_id']], 2 => $contributionStatuses[$fields['contribution_status_id']]));
    if (!contributionStatusChange($form->_defaultValues['contribution_status_id'], $fields['contribution_status_id']) 
      && CRM_Utils_Array::value('contribution_status_id', $form->_errors) == $errorMsg) {
      $form->setElementError('contribution_status_id', NULL);
    }
  }
}

function customcontribstatuses_civicrm_pre($op, $objectName, $id, &$params) {
  // over-ride status to avoid creation of financial trxn entries
  if ($objectName == 'Contribution' && $op == 'edit' 
    && !contributionStatusChange($params['prevContribution']->contribution_status_id, $params['contribution_status_id'])) {
    $params['prevContribution']->contribution_status_id = $params['contribution_status_id'];
  }
}

function contributionStatusChange($oldStatus, $newStatus) {
  $coreContributionStatus = array(
    'Completed',
    'Pending',
    'Cancelled',
    'Failed',
    'In Progress',
    'Overdue',
    'Refunded',
    'Partially paid',
    'Pending refund',
  );
  
  $contributionStatuses = CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name');
  if (in_array($contributionStatuses[$oldStatus], $coreContributionStatus)) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function customcontribstatuses_civicrm_postInstall() {
}
