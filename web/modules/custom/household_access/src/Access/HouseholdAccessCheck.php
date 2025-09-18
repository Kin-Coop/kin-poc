<?php

namespace Drupal\household_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;

/**
 * Checks access for household-based views.
 */
class HouseholdAccessCheck implements AccessInterface {

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check access for.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, Route $route) {
    // This is handled by the Views access plugin
    // This is just a placeholder for the route requirement
    $current_user = \Drupal::currentUser();
    $group_id = 0;
    $url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    $path_args = explode('/', trim($url, '/'));

    // Safety check: Expecting /member/group/[id]
    if (isset($path_args[2]) && is_numeric($path_args[2])) {
      $group_id = $path_args[2];
    }

    if ($this->_household_access_check_user_household_access($current_user, $group_id)) {
      return AccessResult::allowed();
    } else {
      return AccessResult::forbidden();
    }
  }

  protected function _household_access_check_user_household_access(AccountInterface $account, $group_id) {
    // Allow admin users full access
    if ($account->hasPermission('administer civicrm')) {
      return TRUE;
    }

    // Get the CiviCRM contact ID for the current Drupal user
    $contact_id = $this->_household_access_get_contact_id_from_user($account);

    if (!$contact_id) {
      return FALSE;
    }

    // Check if contact has relationship to the household
    return $this->_household_access_contact_belongs_to_household($contact_id, $group_id);
  }

  protected function _household_access_get_contact_id_from_user(AccountInterface $account) {
    try {
      \Drupal::service('civicrm')->initialize();

      // Get contact ID using CiviCRM API
      $result = civicrm_api3('UFMatch', 'get', [
        'uf_id' => $account->id(),
        'sequential' => 1,
      ]);

      if (!empty($result['values'][0]['contact_id'])) {
        return $result['values'][0]['contact_id'];
      }
    } catch (Exception $e) {
      \Drupal::logger('household_access')->error('Error getting contact ID: @error', ['@error' => $e->getMessage()]);
    }

    return FALSE;
  }

  protected function _household_access_contact_belongs_to_household($contact_id, $group_id) {
    try {
      \Drupal::service('civicrm')->initialize();

      // Check for relationship between contact and household
      $result = civicrm_api3('Relationship', 'get', [
        'contact_id_a' => $contact_id,
        'contact_id_b' => $group_id,
        'is_active' => 1,
        'sequential' => 1,
        'options' => ['limit' => 1],
      ]);

      // Also check reverse relationship (contact_id_b to contact_id_a)
      if (empty($result['values'])) {
        $result = civicrm_api3('Relationship', 'get', [
          'contact_id_a' => $group_id,
          'contact_id_b' => $contact_id,
          'is_active' => 1,
          'sequential' => 1,
          'options' => ['limit' => 1],
        ]);
      }

      return !empty($result['values']);

    } catch (Exception $e) {
      \Drupal::logger('household_access')->error('Error checking household relationship: @error', ['@error' => $e->getMessage()]);
      return FALSE;
    }
  }

}
