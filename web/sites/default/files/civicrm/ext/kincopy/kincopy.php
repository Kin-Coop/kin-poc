<?php

require_once 'kincopy.civix.php';

use CRM_kincopy_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function kincopy_civicrm_config(&$config): void {
    _kincopy_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function kincopy_civicrm_install(): void {
    _kincopy_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function kincopy_civicrm_enable(): void {
    _kincopy_civix_civicrm_enable();
}

function kincopy_civicrm_buildForm($formName, $form) {
  //Civi::log()->debug('Contents of $formName: ' . print_r($formName, TRUE));
  //Civi::log()->debug('Contents of $formName: ' . print_r($form, TRUE));
  if ($formName === 'CRM_Contribute_Form_Contribution_Main') {
    if ($form->_id === 5) {
      if($form->getAction() == CRM_Core_Action::ADD) {
        if (isset($_GET['groupid']) && $_GET['me']) {
          $ref = $_GET['me'] . '-' . date('mdi');
          $defaults['custom_25'] = $_GET['groupid'];
          $defaults['custom_61'] = $ref;
          //Civi::log()->debug('Contents of $defaults: ' . print_r($form->_fields, TRUE));
        }
        $defaults['custom_66'] = 2;
        $form->setDefaults($defaults);
        $form->addRule('custom_25', ts('This field is required.'), 'required');
      }
    }
  }
}
