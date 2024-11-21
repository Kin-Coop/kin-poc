<?php

namespace Drupal\opencollective_webhooks\Model;

/**
 * Represents an open collective webhook payload.
 */
interface IncomingWebhookPayloadInterface {

  const CREATED_AT_DATE_FORMAT = 'Y-m-d\TH:i:s.vp';

  const VALID_TYPES = [
    'collective.expense.approved',
    'collective.expense.created',
    'collective.expense.deleted',
    'collective.expense.paid',
    'collective.expense.rejected',
    'collective.expense.updated',
    'collective.member.created',
    'collective.transaction.created',
    'collective.update.published',
  ];

  /**
   * Whether the payload is valid.
   *
   * @return bool
   *   True if valid, otherwise false.
   */
  public function isValid(): bool;

  /**
   * Get the payload ID as defined by open collective.
   *
   * @return int
   *   Payload ID.
   */
  public function getId(): int;

  /**
   * Get created at datetime.
   *
   * @return string
   *   Datetime string.
   */
  public function getCreatedAt(): string;

  /**
   * Get created at unix timestamp.
   *
   * @return int
   *   Created timestamp.
   */
  public function getCreatedAtTimestamp(): int;

  /**
   * Get the type of the payload.
   *
   * @return string
   *   Payload type.
   */
  public function getType(): string;

  /**
   * Get the collective ID for the payload.
   *
   * @return int
   *   Collective ID.
   */
  public function getCollectiveId(): int;

  /**
   * Get the payload's 'data' array.
   *
   * @return array
   *   Data array.
   */
  public function getData(): array;

  /**
   * Get the entire payload array.
   *
   * @return array
   *   Payload array.
   */
  public function getPayload(): array;

}
