<?php

namespace Drupal\kin_civi\Service;

\Drupal::service('civicrm')->initialize();

use Drupal\Core\Session\AccountInterface;
use Civi\Api4\UFMatch;

class HouseholdAccessChecker {

  public function getContactId($uid)
  {
    try {
      // Query CiviCRM APIv4 to get the contact ID for the Drupal user.
      $result = UFMatch::get(FALSE)
        ->addWhere('uf_id', '=', $uid)
        ->addSelect('contact_id')
        ->execute();

      if (isset($result[0])) {
        return (int)$result->first()['contact_id'];
      } else {
        return FALSE;
      }
    } catch (APIException $e) {
      \Drupal::logger('mymodule')->error('CiviCRM APIv4 error: @message', ['@message' => $e->getMessage()]);
    }
  }

  public function isInHousehold($contact_id, $group_id) {
    try {
      $relationships = \Civi\Api4\Relationship::get(FALSE)
        ->addSelect('*')
        ->addWhere('contact_id_a', '=', $contact_id)
        ->addWhere('contact_id_b', '=', $group_id)
        ->setLimit(1)
        ->execute();

      if (empty($relationships[0])) {
        return false;
      } else {
        return true;
      }
    }
    catch (CiviCRM_API4_Exception $e) {
      \Civi::log()->error("API error during email lookup: " . $e->getMessage());
    }
  }

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

  /*
  public function access($uid1,$uid2, Route $route) {
    //return $this->isUserTheGatekeeper($account) || $this->isUserTheKeymaster($account);
    return FALSE;
  }
  */

  private function getContactIdFromUid($uid): ?int {
    $match = civicrm_api3('UFMatch', 'get', ['uf_id' => $uid]);
    return $match['count'] ? $match['values'][$match['id']]['contact_id'] : null;
  }
}
