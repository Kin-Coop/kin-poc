<?php

namespace Drupal\opencollective_webhooks\Event;

use Drupal\opencollective_webhooks\Entity\WebhookEventInterface;

/**
 * Represents an object that will be dispatched by the event dispatcher service.
 */
interface IncomingWebhookEventInterface {

  const PAYLOAD_DATE_FORMAT = 'Y-m-d\TH:i:s.vp';

  /**
   * Get the Drupal WebhookEvent entity for this event.
   *
   * @return \Drupal\opencollective_webhooks\Entity\WebhookEventInterface
   *   WebhookEvent entity.
   */
  public function getWebhookEventEntity(): WebhookEventInterface;

  /**
   * Convert to an array.
   *
   * @return array
   *   Array representing the object.
   */
  public function toArray(): array;

}
