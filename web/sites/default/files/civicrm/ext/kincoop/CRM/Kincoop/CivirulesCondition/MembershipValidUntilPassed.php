<?php

use Civi\Api4\Contact;
use CRM_Kincoop_ExtensionUtil as E;

/**
 * CiviRules condition: TRUE when the contact's
 * Membership.Membership_Valid_Until date is in the past.
 *
 * Intended for the "Contribution is added" trigger, where the contact
 * under evaluation is the contribution's contact.
 */
class CRM_Kincoop_CivirulesCondition_MembershipValidUntilPassed extends CRM_Civirules_Condition {

  /**
   * No extra configuration form is needed for this condition.
   *
   * @param int $ruleConditionId
   * @return bool
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return FALSE;
  }

  /**
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    // For the "Contribution is added" trigger this is the contribution's contact.
    $contactId = $triggerData->getContactId();
    if (empty($contactId)) {
      return FALSE;
    }

    try {
      $validUntil = Contact::get(FALSE)
        ->addSelect('Membership.Membership_Valid_Until')
        ->addWhere('id', '=', $contactId)
        ->execute()
        ->first()['Membership.Membership_Valid_Until'] ?? NULL;
    }
    catch (\Exception $e) {
      return FALSE;
    }

    // No date set -> we can't say it has passed.
    if (empty($validUntil)) {
      return FALSE;
    }

    // Date-only comparison: becomes TRUE the day AFTER the valid-until date,
    // i.e. the membership is still valid ON the date itself.
    // Change "<" to "<=" if you want it TRUE on the valid-until date too.
    $validUntilDate = new DateTimeImmutable($validUntil);
    $today = new DateTimeImmutable('today');

    return $validUntilDate < $today;
  }

  /**
   * Human-readable label shown in the CiviRules UI.
   *
   * @return string
   */
  public function userFriendlyConditionParams() {
    return E::ts('Membership valid-until date has passed');
  }

}
