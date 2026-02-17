<?php

namespace Drupal\kin_welcome\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for welcome modal actions.
 */
class WelcomeModalController extends ControllerBase {

  /**
   * Mark the welcome modal as shown for the current user.
   */
  public function markShown(Request $request) {
    $current_user = $this->currentUser();

    if ($current_user->isAnonymous()) {
      return new JsonResponse(['status' => 'error', 'message' => 'Not logged in'], 403);
    }

    // Mark in user data so it never shows again
    \Drupal::service('user.data')->set(
      'kin_welcome',
      $current_user->id(),
      'has_seen_welcome_modal',
      TRUE
    );

    // Remove session flag
    $request->getSession()->remove('show_welcome_modal');

    return new JsonResponse(['status' => 'success']);
  }

}
