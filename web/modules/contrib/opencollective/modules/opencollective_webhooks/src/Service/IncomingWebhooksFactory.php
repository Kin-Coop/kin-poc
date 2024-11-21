<?php

namespace Drupal\opencollective_webhooks\Service;

use Drupal\Component\Datetime\Time;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Http\RequestStack;
use Drupal\opencollective_webhooks\Entity\WebhookEvent;
use Drupal\opencollective_webhooks\Entity\WebhookEventInterface;
use Drupal\opencollective_webhooks\Event\IncomingWebhookEvent;
use Drupal\opencollective_webhooks\Event\IncomingWebhookEventInterface;
use Drupal\opencollective_webhooks\Model\IncomingWebhookPayload;
use Drupal\opencollective_webhooks\Model\IncomingWebhookPayloadInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Default implementation of the incoming webhooks factory.
 */
class IncomingWebhooksFactory implements IncomingWebhooksFactoryInterface {

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  private Time $time;

  /**
   * Construct.
   *
   * @param \Drupal\Component\Datetime\Time $time
   *   Time service.
   */
  public function __construct(Time $time) {
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function createWebhookPayloadFromRequest(Request $request): ?IncomingWebhookPayloadInterface {
    $payload = Json::decode($request->getContent());
    if (!$payload) {
      return NULL;
    }

    return $this->createWebhookPayload($payload);
  }

  /**
   * {@inheritdoc}
   */
  public function createWebhookPayload(array $payload): IncomingWebhookPayloadInterface {
    return new IncomingWebhookPayload($payload);
  }

  /**
   * {@inheritdoc}
   */
  public function createWebhookEventEntityFromRequest(Request $request): WebhookEventInterface {
    $payload = $this->createWebhookPayloadFromRequest($request);

    return $this->createWebhookEventEntityFromPayload($payload, $this->getRequestContext($request));
  }

  /**
   * {@inheritdoc}
   */
  public function createWebhookEventEntityFromPayload(IncomingWebhookPayloadInterface $payload, array $context = []): WebhookEventInterface {
    return WebhookEvent::create([
      'event_received_at' => $this->time->getRequestTime(),
      'event_context' => Json::encode($context),
      'payload_id' => $payload?->getId(),
      'payload_created_at' => $payload?->getCreatedAtTimestamp(),
      'payload_type' => $payload?->getType(),
      'payload_collective_id' => $payload?->getCollectiveId(),
      'payload' => Json::encode($payload?->getPayload() ?? []),
    ]);
  }

  /**
   * Get additional details about the request.
   *
   * @return array
   *   Request contextual details.
   */
  protected function getRequestContext(Request $request): array {
    return [
      'request_uri' => $request->getRequestUri(),
      'request_datetime_formatted' => \date(IncomingWebhookPayloadInterface::CREATED_AT_DATE_FORMAT, $this->time->getRequestTime()),
    ];
  }

}
