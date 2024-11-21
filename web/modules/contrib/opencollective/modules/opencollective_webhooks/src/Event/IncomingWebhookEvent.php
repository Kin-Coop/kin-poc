<?php

namespace Drupal\opencollective_webhooks\Event;

use Drupal\opencollective_webhooks\Entity\WebhookEventInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Default implementation of WebhookEvent event.
 */
class IncomingWebhookEvent extends Event implements IncomingWebhookEventInterface {

  /**
   * WebhookEvent Entity.
   *
   * @var \Drupal\opencollective_webhooks\Entity\WebhookEventInterface
   */
  private WebhookEventInterface $webhookEvent;

  /**
   * Construct.
   *
   * @param \Drupal\opencollective_webhooks\Entity\WebhookEventInterface $webhookEvent
   *   WebhookEvent Entity.
   */
  public function __construct(WebhookEventInterface $webhookEvent) {
    $this->webhookEvent = $webhookEvent;
  }

  /**
   * {@inheritdoc}
   */
  public function getWebhookEventEntity(): WebhookEventInterface {
    return $this->webhookEvent;
  }

  /**
   * {@inheritdoc}
   */
  public function toArray(): array {
    return [
      'event_id' => $this->webhookEvent->id(),
      'event_received_at' => $this->webhookEvent->eventReceivedAt(),
      'event_context' => $this->webhookEvent->eventContext(),
      'payload_id' => $this->webhookEvent->payloadId(),
      'payload_created_at' => $this->webhookEvent->payloadCreatedAt(),
      'payload_type' => $this->webhookEvent->payloadType(),
      'payload_collective_id' => $this->webhookEvent->payloadCollectiveId(),
      'payload' => $this->webhookEvent->payload()->getPayload(),
    ];
  }

}
