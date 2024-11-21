<?php

namespace Drupal\opencollective_webhooks\Service;

use Drupal\opencollective_webhooks\Entity\WebhookEventInterface;
use Drupal\opencollective_webhooks\Model\IncomingWebhookPayloadInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for object factory.
 */
interface IncomingWebhooksFactoryInterface {

  /**
   * Create an incoming webhook payload from the current request.
   *
   * @return \Drupal\opencollective_webhooks\Model\IncomingWebhookPayloadInterface|null
   *   Webhook payload if successful, otherwise null.
   */
  public function createWebhookPayloadFromRequest(Request $request): ?IncomingWebhookPayloadInterface;

  /**
   * Create an incoming webhook payload with the given data.
   *
   * @param array $payload
   *   Event data.
   *
   * @return \Drupal\opencollective_webhooks\Model\IncomingWebhookPayloadInterface
   *   IncomingWebhookPayload instance.
   */
  public function createWebhookPayload(array $payload): IncomingWebhookPayloadInterface;

  /**
   * Get a new WebhookEVent entity from the given webhook payload request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   *
   * @return \Drupal\opencollective_webhooks\Entity\WebhookEventInterface
   *   WebhookEvent entity instance, unsaved.
   */
  public function createWebhookEventEntityFromRequest(Request $request): WebhookEventInterface;

  /**
   * Get a new WebhookEVent entity from the given webhook payload.
   *
   * @param \Drupal\opencollective_webhooks\Model\IncomingWebhookPayloadInterface $payload
   *   Payload instance.
   * @param array $context
   *   Additional context about the webhook payload.
   *
   * @return \Drupal\opencollective_webhooks\Entity\WebhookEventInterface
   *   WebhookEvent entity instance, unsaved.
   */
  public function createWebhookEventEntityFromPayload(IncomingWebhookPayloadInterface $payload, array $context = []): WebhookEventInterface;

}
