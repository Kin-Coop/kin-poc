<?php

namespace Drupal\opencollective_webhooks;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for the webhook event entity type.
 */
class WebhookEventListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();

    $total = $this->getStorage()
      ->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    $build['summary']['#markup'] = $this->t('Total webhook events: @total', ['@total' => $total]);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['event_id'] = $this->t('Event ID');
    $header['payload_created_at'] = $this->t('Created At');
    $header['event_received_at'] = $this->t('Received At');
    // $header['payload_id'] = $this->t('Payload ID');
    $header['payload_type'] = $this->t('Payload Type');
    // $header['payload_collective_id'] = $this->t('Collective ID');
    $header['payload_context'] = $this->t('Payload and Context');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\opencollective_webhooks\Entity\WebhookEventInterface $entity */
    $row['event_id'] = $entity->toLink();
    $row['payload_created_at'] = \date('Y-m-d H:i:s', $entity->payloadCreatedAt());
    $row['event_received_at'] = \date('Y-m-d H:i:s', $entity->eventReceivedAt());
    // $row['payload_id'] = $entity->payloadId();
    $row['payload_type'] = $entity->payloadType();
    // $row['payload_collective_id'] = $entity->payloadCollectiveId();
    $row['payload_context']['style'] = 'min-width: 40%;';
    $row['payload_context']['data'] = [
      '#type' => 'container',
      'payload' => [
        '#theme' => 'opencollective_debug',
        '#title' => $this->t('Payload'),
        '#debug' => $entity->payload()->getPayload(),
      ],
      'context' => [
        '#theme' => 'opencollective_debug',
        '#title' => $this->t('Payload'),
        '#debug' => $entity->eventContext(),
      ],
    ];
    return $row + parent::buildRow($entity);
  }

}
