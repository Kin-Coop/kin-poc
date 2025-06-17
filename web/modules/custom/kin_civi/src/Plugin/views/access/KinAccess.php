<?php

namespace Drupal\kin_civi\Plugin\views\access;

use Drupal\Core\Access\AccessResult;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;

/**
 * Restrict access to household members.
 *
 * @ViewsAccess(
 *   id = "kin_access",
 *   title = @Translation("Kin Member Access")
 * )
 */
class KinAccess extends AccessPluginBase {

  protected $accessChecker;

  /**
   * Required by AccessPluginInterface.
   */
  public function alterRouteDefinition(Route $route) {
    // Nothing to modify here for this use case.
    // This method must be present to fulfill the interface.
    $route->setRequirement('_access', 'TRUE') ;
    //return FALSE;
  }

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $accessChecker) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->accessChecker = $accessChecker;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('kin_civi.kin_access'));
  }

  public function access(AccountInterface $account) {

    //return AccessResult::forbidden();

    //$is_allowed = $this->checkUserIsInHousehold($account);
    $is_allowed = FALSE;

    //return false;
    return AccessResult::forbidden();


    if ($is_allowed) {
      return AccessResult::allowed();
        //->addCacheContexts(['user'])
        //->addCacheTags(['civicrm_contact']); // Adjust as needed
    }
    else {
      return AccessResult::forbidden();
        //->addCacheContexts(['user'])
        //->addCacheTags(['civicrm_contact']);
    }
  }
}
