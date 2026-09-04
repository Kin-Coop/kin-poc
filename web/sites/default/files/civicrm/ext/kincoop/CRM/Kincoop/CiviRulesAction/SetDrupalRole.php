<?php

use CRM_Kincoop_ExtensionUtil as E;

class CRM_Kincoop_CiviRulesAction_SetDrupalRole extends CRM_Civirules_Action {

  /**
   * Swap the Drupal roles for the triggering contact's linked user.
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contactId = $triggerData->getContactId();
    if (!$contactId) {
      return;
    }
    $uid = CRM_Core_BAO_UFMatch::getUFId($contactId);
    if (!$uid) {
      return;
    }

    // Defer the Drupal entity write out of the fiber context.
      \Drupal::service('kernel')->addResponseListenerOrShutdown ?? NULL; // (see note)
    register_shutdown_function(
      ['CRM_Kincoop_CiviRulesAction_SetDrupalRole', 'swapRole'],
      (int) $uid
    );
  }

  public static function swapRole($uid) {
    $user = \Drupal\user\Entity\User::load($uid);
    if (!$user) {
      return;
    }
    if ($user->hasRole('pending_member')) {
      $user->removeRole('pending_member');
    }
    if (!$user->hasRole('member')) {
      $user->addRole('member');
    }
    $user->save();
  }

  /**
   * No extra config form for this action.
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return FALSE;
  }

}
