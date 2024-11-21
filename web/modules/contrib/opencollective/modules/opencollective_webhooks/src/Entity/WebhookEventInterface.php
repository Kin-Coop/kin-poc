<?php

namespace Drupal\opencollective_webhooks\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\opencollective_webhooks\Model\IncomingWebhookPayloadInterface;

/**
 *
 */
interface WebhookEventInterface extends ContentEntityInterface {

  /**
   * Event id.
   *
   * @return int
   *   Event id.
   */
  public function id();

  /**
   * Generated label.
   *
   * @return string
   *   Label.
   */
  public function label(): string;

  /**
   * Get timestamp for when the webhook was received.
   *
   * @return int
   *   Received at timestamp.
   */
  public function eventReceivedAt(): int;

  /**
   * Additional data about the reception of the webhook.
   *
   * @return array
   *   Additional webhook context.
   */
  public function eventContext(): array;

  /**
   * Timestamp from Open Collective webhook payload.
   *
   * @return int
   *   Timestamp for when payload was created by Open Collective.
   */
  public function payloadCreatedAt(): int;

  /**
   * ID of the payload from Open Collective.
   *
   * @return int
   *   Payload Id.
   */
  public function payloadId(): int;

  /**
   * Payload type.
   *
   * @return string
   *   Payload type.
   */
  public function payloadType(): string;

  /**
   * Collective ID.
   *
   * @return int
   *   Collective ID.
   */
  public function payloadCollectiveId(): int;

  /**
   * Instance of the payload object.
   *
   * @return \Drupal\opencollective_webhooks\Model\IncomingWebhookPayloadInterface
   *   Payload as object instance.
   */
  public function payload(): IncomingWebhookPayloadInterface;

}
