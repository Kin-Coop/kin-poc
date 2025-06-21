<?php

declare(strict_types = 1);

namespace Drupal\watchdog_search\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection): void {
    if ($route = $collection->get('dblog.overview')) {
      $route->setDefault('_controller', '\Drupal\watchdog_search\Controller\DbLogController::buildPage');
    }
  }

}
