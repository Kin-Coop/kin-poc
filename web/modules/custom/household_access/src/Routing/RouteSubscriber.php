<?php

namespace Drupal\household_access\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Add custom access check to routes that require it
    foreach ($collection->all() as $route) {
      if ($route->hasRequirement('_household_access_check')) {
        $route->setRequirement('_custom_access', '\Drupal\household_access\Access\HouseholdAccessCheck::access');
      }
    }
  }

}
