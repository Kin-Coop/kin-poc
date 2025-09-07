<?php
namespace Drupal\kin_forum\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class GroupForumRedirectSubscriber implements EventSubscriberInterface {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onRequest', 100],
    ];
  }

  public function onRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();
    //$path = "";

    // Check if this is a node path
    if (preg_match('/^\/node\/(\d+)$/', $path, $matches)) {
      $node_id = $matches[1];

      try {
        $node = $this->entityTypeManager->getStorage('node')->load($node_id);

        if ($node && $node->bundle() == 'group_forum') {
          // Get the household ID from the field_group field
          if ($node->hasField('field_group') && !$node->get('field_group')->isEmpty()) {
            $household_id = $node->get('field_group')->target_id;

            if ($household_id) {
              // Create the redirect URL
              $redirect_url = Url::fromUserInput("/member/group/{$household_id}/forum");

              // Set the redirect response
              $response = new TrustedRedirectResponse($redirect_url->toString(), 301);
              $event->setResponse($response);
            }
          }
        }
      }
      catch (\Exception $e) {
        // Log the error but don't break the request
        \Drupal::logger('kin_forum_notify')->error('Error in group forum redirect: @message', ['@message' => $e->getMessage()]);
      }
    }
  }
}
