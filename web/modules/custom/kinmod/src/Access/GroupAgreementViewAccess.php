<?php

/**
 * @file
 * Access checker for group agreement viewing.
 *
 * Create this as: modules/custom/kinmod/src/Access/GroupAgreementViewAccess.php
 */

namespace Drupal\kinmod\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * Custom access checker for group agreement viewing.
 */
class GroupAgreementViewAccess implements AccessInterface {

  /**
   * Checks access for group agreement viewing based on household relationship.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, RouteMatchInterface $route_match) {
    // Get the node from the route
    $node = $route_match->getParameter('node');

    // Make sure it's a node and specifically a group_agreement
    if (!$node instanceof NodeInterface || $node->bundle() !== 'group_agreement') {
      return AccessResult::neutral();
    }

    // Check if user has bypass permission (like administrators)
    if ($account->hasPermission('administer nodes') ||
      $account->hasPermission('bypass node access') ||
      $account->hasPermission('view any group_agreement content')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    // Check if the user is the author of the node
    if ($node->getOwnerId() == $account->id()) {
      return AccessResult::allowed()
        ->cachePerPermissions()
        ->cachePerUser()
        ->addCacheableDependency($node);
    }

    // Get the household ID from the node's field
    if (!$node->hasField('field_civi_group') || $node->get('field_civi_group')->isEmpty()) {
      return AccessResult::forbidden('No household associated with this group agreement.')
        ->addCacheableDependency($node);
    }

    $household_id = $node->get('field_civi_group')->target_id;

    // Get CiviCRM contact ID for the current user
    $contact_id = $this->getContactIdFromUser($account);

    if (!$contact_id) {
      return AccessResult::forbidden('User account not linked to CiviCRM contact.')
        ->cachePerUser();
    }

    // Check if contact belongs to the household
    if ($this->contactBelongsToHousehold($contact_id, $household_id)) {
      return AccessResult::allowed()
        ->cachePerUser()
        ->addCacheableDependency($node);
    }

    // Access denied - user doesn't belong to this household
    return AccessResult::forbidden('You do not have permission to view this group agreement.')
      ->cachePerUser()
      ->addCacheableDependency($node);
  }

  /**
   * Get CiviCRM contact ID from Drupal user account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return int|false
   *   The contact ID or FALSE if not found.
   */
  protected function getContactIdFromUser(AccountInterface $account) {
    try {
      \Drupal::service('civicrm')->initialize();

      $result = civicrm_api3('UFMatch', 'get', [
        'uf_id' => $account->id(),
        'sequential' => 1,
      ]);

      if (!empty($result['values'][0]['contact_id'])) {
        return (int) $result['values'][0]['contact_id'];
      }
    } catch (\Exception $e) {
      \Drupal::logger('kinmod')->error('Error getting contact ID for user @uid: @error', [
        '@uid' => $account->id(),
        '@error' => $e->getMessage()
      ]);
    }

    return FALSE;
  }

  /**
   * Check if a contact belongs to a specific household.
   *
   * @param int $contact_id
   *   The contact ID.
   * @param int $household_id
   *   The household ID.
   *
   * @return bool
   *   TRUE if contact belongs to household, FALSE otherwise.
   */
  protected function contactBelongsToHousehold($contact_id, $household_id) {
    try {
      \Drupal::service('civicrm')->initialize();

      // Check contact_id_a -> contact_id_b relationship (contact to household)
      $result = civicrm_api3('Relationship', 'get', [
        'contact_id_a' => $contact_id,
        'contact_id_b' => $household_id,
        'is_active' => 1,
        'sequential' => 1,
        'options' => ['limit' => 1],
      ]);

      if (!empty($result['values'])) {
        return TRUE;
      }

      // Check reverse relationship (household to contact)
      $result = civicrm_api3('Relationship', 'get', [
        'contact_id_a' => $household_id,
        'contact_id_b' => $contact_id,
        'is_active' => 1,
        'sequential' => 1,
        'options' => ['limit' => 1],
      ]);

      return !empty($result['values']);

    } catch (\Exception $e) {
      \Drupal::logger('kinmod')->error('Error checking household relationship for contact @contact_id and household @household_id: @error', [
        '@contact_id' => $contact_id,
        '@household_id' => $household_id,
        '@error' => $e->getMessage()
      ]);
      return FALSE;
    }
  }

}
