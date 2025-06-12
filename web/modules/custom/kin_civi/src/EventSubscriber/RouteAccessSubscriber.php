<?php

namespace Drupal\kin_civi\EventSubscriber;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\kin_civi\Service\HouseholdAccessChecker;

class RouteAccessSubscriber implements EventSubscriberInterface {

  protected $accessChecker;

  public function __construct(HouseholdAccessChecker $accessChecker) {
    $this->accessChecker = $accessChecker;
  }

  public static function getSubscribedEvents() {
    return [
      'kernel.request' => ['checkHouseholdAccess', 30],
    ];
  }

  public function checkHouseholdAccess(RequestEvent $event) {
    $request = $event->getRequest();
    $route = $request->attributes->get('_route');
    $current_user = \Drupal::currentUser();
    $uid1 = $current_user->id();

    if ($route === 'entity.user.canonical') {
      $uid2 = $request->attributes->get('user')->id();
    } elseif ($route === 'private_message.create_form') {
      $uid2 = $request->query->get('recipient');
    } else {
      return;
    }

    if (!$this->accessChecker->isInSameHousehold($uid1, $uid2)) {
      throw new AccessDeniedHttpException('Access denied: Not in the same household.');
    }
  }
}
