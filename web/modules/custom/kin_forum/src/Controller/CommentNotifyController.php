<?php

namespace Drupal\kin_forum\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\PrependCommand;
use Symfony\Component\HttpFoundation\Request;

class CommentNotifyController extends ControllerBase {

  /**
   * Check for new comments since a given timestamp.
   */
  public function checkNewComments(Request $request) {
    $response = new AjaxResponse();

    $node_id = $request->query->get('node_id');
    $last_check = $request->query->get('last_check', 0);

    if (!$node_id) {
      return $response;
    }

    // Get new comments since last check
    $comment_storage = \Drupal::entityTypeManager()->getStorage('comment');
    $query = $comment_storage->getQuery()
      ->condition('entity_id', $node_id)
      ->condition('entity_type', 'node')
      ->condition('created', $last_check, '>')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->accessCheck(TRUE);

    $comment_ids = $query->execute();

    if (!empty($comment_ids)) {
      $new_comment_count = count($comment_ids);

      // Create notification message
      $notification = [
        '#theme' => 'status_messages',
        '#message_list' => [
          'status' => [
            $this->formatPlural(
              $new_comment_count,
              '1 new comment has been added. <a href="#" onclick="location.reload(); return false;">Refresh to see it</a>.',
              '@count new comments have been added. <a href="#" onclick="location.reload(); return false;">Refresh to see them</a>.'
            )
          ]
        ],
        '#attributes' => ['class' => ['new-comment-alert']],
      ];

      $response->addCommand(new PrependCommand('.view-content', $notification));
    }

    return $response;
  }
}
