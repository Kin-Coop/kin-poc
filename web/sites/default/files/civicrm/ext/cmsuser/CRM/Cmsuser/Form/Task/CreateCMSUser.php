<?php

use CRM_Cmsuser_ExtensionUtil as E;

class CRM_Cmsuser_Form_Task_CreateCMSUser extends CRM_Contact_Form_Task {

  public function preProcess() {
    parent::preProcess();

    $this->assign('title', E::ts('Create CMS User'));
    $this->assign('help_text', E::ts('A CMS user will be created for each of these contacts'));

    $this->assign('status',
      E::ts("Selected contacts: %1", [
        1 => count($this->getContactIDs()),
      ])
    );

  }

  public function buildQuickForm() {
    $this->addYesNo('notify_user', E::ts('Notify User?'), NULL, TRUE);
    $this->addDefaultButtons(E::ts('Next'));
  }

  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    $defaults['notify_user'] = 0;
    return $defaults;
  }

  public function postProcess() {
    $stats = [
      'created' => 0,
      'existing' => 0,
      'failed' => 0
    ];
    foreach($this->getContactIDs() as $contactId) {
      try {
        $cmsuserParams = [
          'contact_id' => $contactId,
          'notify' => $this->getSubmittedValue('notify_user'),
        ];

        $result = civicrm_api3('Cmsuser', 'create', $cmsuserParams)['values'];
        if ($result['created']) {
          $stats['created'] += 1;
        }
        else {
          $stats['existing'] += 1;
        }
      }
      catch (Exception $e) {
        $stats['failed'] += 1;
        \Civi::log()->error('Failed to create CMS user for contact ID: ' . $contactId . ': ' . $e->getMessage());
      }
    }
    CRM_Core_Session::setStatus(E::ts('Created %1 CMS users, %2 already had user account, %3 failed', [1 => $stats['created'], 2 => $stats['existing'], 3 => $stats['failed']]), '', 'success');
  }

}
