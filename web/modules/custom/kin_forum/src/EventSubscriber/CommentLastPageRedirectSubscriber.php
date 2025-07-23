<?php

namespace Drupal\kin_forum\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Drupal\views\Views;
use Drupal\comment\Entity\Comment;
use Drupal\node\Entity\Node;

class CommentLastPageRedirectSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest'],
    ];
  }

  public function onKernelRequest(RequestEvent $event) {
   // return;
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // Only apply to your specific view path.
    if (preg_match('#^/member/group/(\d+)/forum$#', $path, $matches)) {
      $group_id = $matches[1];

      $nids = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'group_forum')
        ->condition('field_group', $group_id)
        ->range(0, 1)
        ->execute();

      if (!empty($nids)) {
        $nid = reset($nids);
        $node = Node::load($nid); // ← This is your $node
      }

      // If the user already has a ?page=... param, don't redirect.
      if ($request->query->has('page')) {
        return;
      }

      // Load the view.
      $view = Views::getView('group_forum_comments');
      if ($view) {
        $view->setDisplay('page_1'); // e.g., 'page_1'
        $view->setArguments([$group_id]);
        $view->execute();

        $total_rows = $view->total_rows;
        $pager = $view->pager;
        $items_per_page = $pager ? $pager->getItemsPerPage() : 0;

        if ($total_rows > 0 && $items_per_page > 0) {
          $last_page = (int) floor(($total_rows - 1) / $items_per_page);

          $comment_ids = \Drupal::entityTypeManager()
            ->getStorage('comment')
            ->getQuery()
            ->accessCheck(TRUE)
            ->condition('entity_id', $node->id())
            ->condition('entity_type', 'node')
            ->condition('status', 1)
            ->sort('created', 'DESC')
            ->range(0, 1)
            ->execute();

          if (!empty($comment_ids)) {
            $last_comment_id = reset($comment_ids);
            $anchor = "#comment-{$last_comment_id}";

            $redirect_url = "/member/group/{$group_id}/forum?page={$last_page}{$anchor}";
            $event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse($redirect_url));
          }

          /*
          // Load the last page to get the last comment ID
          $view->pager->setCurrentPage($last_page);
          $view->execute();

          // Get the last comment rendered on this page
          $last_comment = end($view->result);
          if (!empty($last_comment) && isset($last_comment->_entity)) {
            $comment_id = $last_comment->_entity->id();
            $anchor = "#comment-{$comment_id}";

            $redirect_url = "/member/group/{$group_id}/forum?page={$last_page}{$anchor}";
            $event->setResponse(new \Symfony\Component\HttpFoundation\RedirectResponse($redirect_url));
          }
          */

          // If last_page is not 0, redirect there
          //if ($last_page > 0) {
            //$redirect_url = "/member/group/{$group_id}/forum?page={$last_page}";
            //$event->setResponse(new RedirectResponse($redirect_url));
          //}


        }
      }
    }
  }
}
