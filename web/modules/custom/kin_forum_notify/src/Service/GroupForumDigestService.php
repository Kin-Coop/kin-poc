<?php

namespace Drupal\kin_forum_notify\Service;

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
    $last_run = $this->state->get('group_forum_digest_last_run', 0);
    $current_time = \Drupal::time()->getCurrentTime();

    // Get all group_forum nodes
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'group_forum')
      ->condition('status', 1)
      ->accessCheck(FALSE);

    $nids = $query->execute();

    foreach ($nids as $nid) {
      $node = Node::load($nid);
      $this->processNodeDigest($node, $last_run, $current_time);
    }

    // Update last run timestamp
    $this->state->set('group_forum_digest_last_run', $current_time);
  }

  protected function processNodeDigest($node, $last_run, $current_time) {
    // Get new comments since last run
    $comment_storage = $this->entityTypeManager->getStorage('comment');
    $query = $comment_storage->getQuery()
      ->condition('entity_id', $node->id())
      ->condition('entity_type', 'node')
      ->condition('created', $last_run, '>')
      ->condition('status', 1)
      ->accessCheck(FALSE);

    $comment_ids = $query->execute();

    if (empty($comment_ids)) {
      return; // No new comments
    }

    $comment_count = count($comment_ids);

    // Get household contact ID from entity reference field
    $household_contact_id = $node->get('field_household_reference')->target_id;

    // Get household members from CiviCRM
    $household_members = $this->getHouseholdMembers($household_contact_id);

    foreach ($household_members as $contact_id) {
      $user = $this->getUserFromCiviContact($contact_id);
      if ($user) {
        $this->sendDigestToUser($user, $node, $comment_count);
      }
    }
  }

  protected function getHouseholdMembers($household_contact_id) {
    // Use CiviCRM API to get household members
    try {
      $result = civicrm_api3('Relationship', 'get', [
        'contact_id_b' => $household_contact_id,
        'relationship_type_id' => 'Member of Household', // Adjust based on your relationship type
        'is_active' => 1,
      ]);

      $members = [];
      foreach ($result['values'] as $relationship) {
        $members[] = $relationship['contact_id_a'];
      }

      return $members;
    } catch (Exception $e) {
      \Drupal::logger('your_module')->error('Error fetching household members: @message', ['@message' => $e->getMessage()]);
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
      \Drupal::logger('your_module')->error('Error fetching user from contact: @message', ['@message' => $e->getMessage()]);
    }

    return NULL;
  }

  protected function sendDigestToUser($user, $node, $comment_count) {
    // Create message entity
    $message = Message::create([
      'template' => 'group_forum_daily_digest',
      'uid' => $user->id(),
    ]);

    // Set custom field values
    $message->set('field_comment_count', $comment_count);
    $message->set('field_forum_url', $node->toUrl('canonical', ['absolute' => TRUE])->toString());
    $message->save();

    // Send email
    $this->mailManager->mail(
      'your_module',
      'group_forum_digest',
      $user->getEmail(),
      $user->getPreferredLangcode(),
      [
        'message' => $message,
        'user' => $user,
        'node' => $node,
        'comment_count' => $comment_count,
      ]
    );
  }
}
