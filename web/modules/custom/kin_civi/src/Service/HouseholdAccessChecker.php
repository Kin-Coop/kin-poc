<?php

namespace Drupal\kin_civi\Service;

use Drupal\Core\Session\AccountInterface;

class HouseholdAccessChecker {

  /**
   * Check if a Drupal user is in the same CiviCRM household as another.
   */
  public function isInSameHousehold($uid1, $uid2): bool {
    $contact1 = $this->getContactIdFromUid($uid1);
    $contact2 = $this->getContactIdFromUid($uid2);
    if (!$contact1 || !$contact2) {
      return false;
    }

    // Lookup household of contact1
    $rels = civicrm_api3('Relationship', 'get', [
      'contact_id_b' => $contact1,
      'is_active' => 1,
      'relationship_type_id' => 'Household Member of', // update if custom
      'options' => ['limit' => 0],
    ]);

    $household_ids = array_column($rels['values'], 'contact_id_a');

    if (empty($household_ids)) {
      return false;
    }

    // Check if contact2 has any matching household
    $rels2 = civicrm_api3('Relationship', 'get', [
      'contact_id_b' => $contact2,
      'contact_id_a' => ['IN' => $household_ids],
      'relationship_type_id' => 'Household Member of',
      'is_active' => 1,
    ]);

    return $rels2['count'] > 0;
  }

  private function getContactIdFromUid($uid): ?int {
    $match = civicrm_api3('UFMatch', 'get', ['uf_id' => $uid]);
    return $match['count'] ? $match['values'][$match['id']]['contact_id'] : null;
  }
}
