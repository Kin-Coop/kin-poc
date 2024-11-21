<?php

namespace Drupal\opencollective_webhooks\Model;

/**
 * Default implementation of an open collective webhook payload.
 */
class IncomingWebhookPayload implements IncomingWebhookPayloadInterface {

  /**
   * Payload data array from Open Collective.
   *
   * @var array
   */
  protected array $payload;

  /**
   * Construct.
   *
   * @param array $payload
   *   Payload data array from Open Collective.
   */
  public function __construct(array $payload) {
    $this->payload = $payload;
  }

  /**
   * {@inheritdoc}
   */
  public function isValid(): bool {
    return (
      array_key_exists('type', $this->getPayload()) &&
      // @todo Validate the payload type.
      array_key_exists('id', $this->getPayload()) &&
      array_key_exists('createdAt', $this->getPayload()) &&
      array_key_exists('CollectiveId', $this->getPayload()) &&
      array_key_exists('data', $this->getPayload())
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getId(): int {
    return (int) $this->payload['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedAt(): string {
    return $this->payload['createdAt'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedAtTimestamp(): int {
    $date = \DateTime::createFromFormat(IncomingWebhookPayloadInterface::CREATED_AT_DATE_FORMAT, $this->getCreatedAt());
    return $date->getTimestamp();
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return $this->payload['type'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectiveId(): int {
    return (int) $this->payload['CollectiveId'];
  }

  /**
   * {@inheritdoc}
   */
  public function getData(): array {
    return $this->payload['data'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPayload(): array {
    return $this->payload;
  }

}
