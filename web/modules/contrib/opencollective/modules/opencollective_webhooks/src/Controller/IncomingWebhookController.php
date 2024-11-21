<?php

namespace Drupal\opencollective_webhooks\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Http\RequestStack;
use Drupal\opencollective_webhooks\Event\IncomingWebhookEvent;
use Drupal\opencollective_webhooks\Service\IncomingWebhooksFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for Open Collective - Incoming Webhooks routes.
 */
class IncomingWebhookController extends ControllerBase {

  /**
   * Module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $config;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private EventDispatcherInterface $eventDispatcher;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  private ?Request $request;

  /**
   * Webhooks factory.
   *
   * @var \Drupal\opencollective_webhooks\Service\IncomingWebhooksFactoryInterface
   */
  private IncomingWebhooksFactoryInterface $factory;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * Incoming webhooks' controller.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   Event dispatcher.
   * @param \Drupal\Core\Http\RequestStack $requestStack
   *   Request stack.
   * @param \Drupal\opencollective_webhooks\Service\IncomingWebhooksFactoryInterface $factory
   *   Webhooks factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EventDispatcherInterface $eventDispatcher,
    RequestStack $requestStack,
    IncomingWebhooksFactoryInterface $factory,
    LoggerInterface $logger
  ) {
    $this->config = $configFactory->get('opencollective_webhooks.settings');
    $this->eventDispatcher = $eventDispatcher;
    $this->request = $requestStack->getCurrentRequest();
    $this->factory = $factory;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('event_dispatcher'),
      $container->get('request_stack'),
      $container->get('opencollective_webhooks.incoming_webhooks_factory'),
      $container->get('opencollective_webhooks.logger')
    );
  }

  /**
   * Handle the incoming webhook by dispatching an event.
   */
  public function handle(string $incoming_webhook_secret): JsonResponse {
    $this->logger->debug("Incoming webhook: @request_type -- @content", [
      '@request_type' => $this->request->getMethod(),
      '@content' => $this->request->getContent(),
    ]);
    if ($this->config->get('incoming_webhook_secret') !== $incoming_webhook_secret) {
      $this->logger->error("Incoming webhook secret did not match the config value.");
      return new JsonResponse(['message' => 'Forbidden'], 403);
    }

    // Instantiate and validate the request payload.
    $payload = $this->factory->createWebhookPayloadFromRequest($this->request);
    if (!$payload) {
      $this->logger->error("Incoming webhook payload was not found or wasn't able to be decoded.");
      return new JsonResponse(['message' => "Unable to decode payload."], 406);
    }

    if ($payload->isValid()) {
      $webhook_event_entity = $this->factory->createWebhookEventEntityFromRequest($this->request);
      $webhook_event_entity->save();
      $event = new IncomingWebhookEvent($webhook_event_entity);

      $event_names = [
        "opencollective.{$webhook_event_entity->payloadType()}",
        "opencollective.webhook_event",
      ];
      foreach ($event_names as $event_name) {
        $event = $this->eventDispatcher->dispatch($event, $event_name);
      }

      $message = "Dispatched {$event_names[0]} and {$event_names[1]} events for payload id: {$event->getPayload()->getId()}.";
      $this->logger->info($message);
      return new JsonResponse(['message' => $message]);
    }

    $this->logger->warning("Payload found was invalid. Type: @type, Payload: @payload", [
      '@type' => $payload->getType(),
      '@payload' => print_r($payload->getPayload(), 1),
    ]);
    return new JsonResponse(['message' => 'Invalid payload.'], 406);
  }

}
