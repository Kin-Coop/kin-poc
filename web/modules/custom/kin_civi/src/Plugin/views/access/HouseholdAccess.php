<?php

namespace Drupal\kin_civi\Plugin\views\access;

use Drupal\Core\Access\AccessResult;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;

/**
 * Restrict access to household members.
 *
 * @ViewsAccess(
 *   id = "household_access",
 *   title = @Translation("Household Member Access")
 * )
 */
class HouseholdAccess extends AccessPluginBase {

  protected $accessChecker;

  /**
   * Required by AccessPluginInterface.
   */
  public function alterRouteDefinition(Route $route) {
    // Nothing to modify here for this use case.
    // This method must be present to fulfill the interface.
  }

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $accessChecker) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->accessChecker = $accessChecker;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('kin_civi.household_access'));
  }

  public function access(AccountInterface $account) {
    /*
    $household_id = \Drupal::routeMatch()->getParameter('household_id');
    if (!$household_id) {
      return FALSE;
    }
    $current_uid = $account->id();
    return $this->accessChecker->isInSameHousehold($current_uid, $this->getUidFromHousehold($household_id));

    */
    // Your logic to check household membership.
    $is_allowed = TRUE;
    //$is_allowed = $this->checkUserIsInHousehold($account);

    if ($is_allowed) {
      return AccessResult::allowed()
        ->addCacheContexts(['user'])
        ->addCacheTags(['civicrm_contact']); // Adjust as needed
    }
    else {
      return AccessResult::forbidden()
        ->addCacheContexts(['user'])
        ->addCacheTags(['civicrm_contact']);
    }
  }

  private function getUidFromHousehold($household_id): ?int {
    $match = civicrm_api3('UFMatch', 'get', [
      'contact_id' => $household_id,
    ]);
    return $match['count'] ? $match['values'][$match['id']]['uf_id'] : null;
  }
}
