<?php

namespace Drupal\kin_forum_notify\Service;

\Drupal::service('civicrm')->initialize();

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\message\Entity\Message;
use Drupal\node\Entity\Node;

class GroupForumDigestService {

  protected $entityTypeManager;
  protected $mailManager;
  protected $state;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, MailManagerInterface $mail_manager, StateInterface $state) {
    $this->entityTypeManager = $entity_type_manager;
    $this->mailManager = $mail_manager;
    $this->state = $state;
  }

  public function sendDailyDigest() {
    //$last_run = $this->state->get('group_forum_digest_last_run', 0);
    $current_time = \Drupal::time()->getCurrentTime();
    $today = date('Y-m-d', $current_time);

    // Get all group_forum nodes
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'group_forum')
      ->condition('status', 1)
      ->accessCheck(FALSE);

    $nids = $query->execute();

    foreach ($nids as $nid) {
      $node = Node::load($nid);
      // Check if we've already sent emails for this node today
      if ($this->hasEmailBeenSentToday($nid, $today)) {
        continue; // Skip this node - already sent today
      }

      // Check if there are new comments and send emails
      if ($this->processNodeDigest($node, $current_time)) {
        // Mark that we've sent emails for this node today
        $this->markEmailSentToday($nid, $today);
      }
    }

    // Update last run timestamp
    //$this->state->set('group_forum_digest_last_run', $current_time);
  }

  protected function hasEmailBeenSentToday($nid, $today) {
    $sent_dates = $this->state->get('kin_forum_notify_sent_dates', []);
    return isset($sent_dates[$nid]) && $sent_dates[$nid] === $today;
  }

  protected function markEmailSentToday($nid, $today) {
    $sent_dates = $this->state->get('kin_forum_notify_sent_dates', []);
    $sent_dates[$nid] = $today;

    // Clean up old entries (keep only last 7 days to prevent bloat)
    $cutoff_date = date('Y-m-d', strtotime('-7 days'));
    foreach ($sent_dates as $node_id => $date) {
      if ($date < $cutoff_date) {
        unset($sent_dates[$node_id]);
      }
    }

    $this->state->set('kin_forum_notify_sent_dates', $sent_dates);
  }

  protected function processNodeDigest($node, $current_time) {
    // Get new comments since last run
    $yesterday = $current_time - 86400; // 24 hours ago

    $comment_storage = $this->entityTypeManager->getStorage('comment');
    $query = $comment_storage->getQuery()
       ->condition('entity_id', $node->id())
       ->condition('entity_type', 'node')
       ->condition('created', $yesterday, '>')
       ->condition('status', 1)
       ->accessCheck(FALSE);

    $comment_ids = $query->execute();

    if (empty($comment_ids)) {
      return FALSE; // No new comments
    }

    $comment_count = count($comment_ids);

    // Get all comment authors for the new comments
    $comment_authors = $this->getCommentAuthors($comment_ids);

    $author_count = count($comment_authors);

    // Get household contact ID from entity reference field
    $household_contact_id = $node->get('field_group')->target_id;

    if (!$household_contact_id) {
      return FALSE; // No household reference, skip
    }

    // Get household members from CiviCRM
    $household_members = $this->getHouseholdMembers($household_contact_id);

    $emails_sent = FALSE;

    // Only send a notification email to everyone if there is more than one comment author
    // If there is only one comment author for new comments then don't send a notification email to that author
    if ($author_count > 1) {
      foreach ($household_members as $contact_id) {
        $user = $this->getUserFromCiviContact($contact_id);
        if ($user) {
          $this->sendDigestToUser($user, $node, $comment_count, $household_contact_id);
          $emails_sent = TRUE;
        }
      }
    } elseif ($author_count == 1) {
      foreach ($household_members as $contact_id) {
        $user = $this->getUserFromCiviContact($contact_id);
        if ($user) {
          // Check if this user is one of the comment authors
          if (!in_array($user->id(), $comment_authors)) {
            $this->sendDigestToUser($user, $node, $comment_count, $household_contact_id);
            $emails_sent = TRUE;
          }
          // If user is a comment author, skip sending them the notification
        }
      }
    }

    return $emails_sent; // Return whether any emails were actually sent
  }

  /**
   * Get all unique user IDs who authored the comments.
   */
  protected function getCommentAuthors($comment_ids) {
    if (empty($comment_ids)) {
      return [];
    }

    $comment_storage = $this->entityTypeManager->getStorage('comment');
    $comments = $comment_storage->loadMultiple($comment_ids);

    $authors = [];
    foreach ($comments as $comment) {
      $author_id = $comment->getOwnerId();
      if ($author_id && !in_array($author_id, $authors)) {
        $authors[] = $author_id;
      }
    }

    return $authors;
  }

  protected function getHouseholdMembers($household_contact_id) {
    // Use CiviCRM API to get household members
    try {
      $result = civicrm_api3('Relationship', 'get', [
        'sequential' => 1,
        'contact_id_b' => $household_contact_id,
        'is_active' => 1,
      ]);

      $members = [];
      foreach ($result['values'] as $relationship) {
        $members[] = $relationship['contact_id_a'];
      }

      return $members;
    } catch (Exception $e) {
      \Drupal::logger('kin_forum_notify')->error('Error fetching household members: @message', ['@message' => $e->getMessage()]);
      return [];
    }
  }

  protected function getUserFromCiviContact($contact_id) {
    // Get Drupal user from CiviCRM contact
    try {
      $result = civicrm_api3('UFMatch', 'get', [
        'contact_id' => $contact_id,
      ]);

      if (!empty($result['values'])) {
        $uf_match = reset($result['values']);
        return \Drupal\user\Entity\User::load($uf_match['uf_id']);
      }
    } catch (Exception $e) {
      \Drupal::logger('kin_forum_notify')->error('Error fetching user from contact: @message', ['@message' => $e->getMessage()]);
    }

    return NULL;
  }

  protected function sendDigestToUser($user, $node, $comment_count, $household_contact_id) {
    // Get first name from CiviCRM
    $contact_id = $this->getContactIdFromUser($user);
    $first_name = $this->getContactFirstName($contact_id);
    $household = $this->getHouseholdName($household_contact_id);

    // Create message entity
    $message = Message::create([
      'template' => 'group_forum_daily_digest',
      'uid' => $user->id(),
    ]);

    // Set custom field values
    $message->set('field_comment_count', $comment_count);
    $message->set('field_forum_url', $node->toUrl('canonical', ['absolute' => TRUE])->toString());
    $message->save();

    $params = compact('message', 'user', 'node', 'comment_count', 'first_name', 'household', 'household_contact_id');
    $params['headers'] = [
      'Content-Type' => 'text/html; charset=UTF-8',
      'MIME-Version' => '1.0',
    ];

    // Send email
    $this->mailManager->mail(
      'kin_forum_notify',
      'group_forum_digest',
      $user->getEmail(),
      $user->getPreferredLangcode(),
      $params
    );
  }

  protected function getContactIdFromUser($user) {
    try {
      $result = civicrm_api3('UFMatch', 'get', [
        'uf_id' => $user->id(),
      ]);

      if (!empty($result['values'])) {
        $uf_match = reset($result['values']);
        return $uf_match['contact_id'];
      }
    } catch (Exception $e) {
      \Drupal::logger('kin_forum_notify')->error('Error fetching contact from user: @message', ['@message' => $e->getMessage()]);
    }

    return NULL;
  }

  protected function getContactFirstName($contact_id) {
    if (!$contact_id) {
      return 'Friend'; // Fallback if no contact found
    }

    try {
      $result = civicrm_api3('Contact', 'get', [
        'id' => $contact_id,
        'return' => ['first_name'],
      ]);

      if (!empty($result['values'])) {
        $contact = reset($result['values']);
        return !empty($contact['first_name']) ? $contact['first_name'] : 'Friend';
      }
    } catch (Exception $e) {
      \Drupal::logger('kin_forum_notify')->error('Error fetching contact first name: @message', ['@message' => $e->getMessage()]);
    }

    return 'Friend'; // Fallback
  }

  protected function getHouseholdName($household_contact_id) {
    if (!$household_contact_id) {
      return ''; // Fallback if no contact found
    }

    try {
      $result = civicrm_api3('Contact', 'get', [
        'id' => $household_contact_id,
        'return' => ['display_name'],
      ]);

      if (!empty($result['values'])) {
        $household = reset($result['values']);
        return !empty($household['display_name']) ? $household['display_name'] : '';
      }
    } catch (Exception $e) {
      \Drupal::logger('kin_forum_notify')->error('Error fetching household name: @message', ['@message' => $e->getMessage()]);
    }

    return 'Friend'; // Fallback
  }
}

