<?php

namespace Drupal\opencollective_webhooks\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Http\RequestStack;
use Drupal\opencollective_webhooks\Entity\WebhookEventInterface;
use Drupal\opencollective_webhooks\Service\PollingTokenManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Polling controller manages the js polling requests.
 */
class PollingController extends ControllerBase {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  private Request $request;

  /**
   * Module settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $config;

  /**
   * Polling access token manager.
   *
   * @var \Drupal\opencollective_webhooks\Service\PollingTokenManagerInterface
   */
  private PollingTokenManagerInterface $jsEventsPollingTokenManager;

  /**
   * Construct.
   *
   * @param \Drupal\Core\Http\RequestStack $requestStack
   *   Request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\opencollective_webhooks\Service\PollingTokenManagerInterface $jsEventsPollingTokenManager
   *   Polling access token manager.
   */
  public function __construct(
    RequestStack $requestStack,
    ConfigFactoryInterface $configFactory,
    PollingTokenManagerInterface $jsEventsPollingTokenManager
  ) {
    $this->request = $requestStack->getCurrentRequest();
    $this->config = $configFactory->get('opencollective_webhooks.settings');
    $this->jsEventsPollingTokenManager = $jsEventsPollingTokenManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('config.factory'),
      $container->get('opencollective_webhooks.polling_token_manager')
    );
  }

  /**
   * Handle the polling request.
   *
   * @param string $access_token
   *   Access token for the polling js.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function handle(string $access_token) {
    if (!$this->jsEventsPollingTokenManager->isTokenValid($access_token)) {
      return new JsonResponse([
        'success' => FALSE,
        'message' => 'Invalid token',
      ]);
    }

    $response = $this->longPollingServer();
    return new JsonResponse($response);
  }

  /**
   * Handle long polling logic.
   *
   * @return array
   *   Response array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function longPollingServer(): array {
    $last_event_id = $this->request->get('lastEventId');
    if (!isset($last_event_id)) {
      return [
        'success' => FALSE,
        'message' => 'Missing lastEventId.',
      ];
    }

    $poll_length = $this->config->get('poll_length');
    $start_time = \time();
    $end_time = \time() + $poll_length;

    do {
      // Look for new events.
      $found = $this->getEventsSince($last_event_id);
      if (!empty($found)) {
        return [
          'success' => TRUE,
          'message' => 'Found ' . count($found) . ' new webhook events.',
          'updatedLastEventId' => $this->getLastEventId(),
          'data' => array_map(function (WebhookEventInterface $event) {
            return ['eventLogId' => $event->id()] + $event->payload()->getPayload();
          }, $found),
        ];
      }

      // If poll_length isn't disabled, sleep and loop again.
      if ($poll_length) {
        usleep(500);
      }
    } while (\time() < $end_time);

    return [
      'success' => FALSE,
      'message' => 'Nothing new found.',
      'startTime' => $start_time,
      'endTime' => $end_time,
      'timeDiff' => $end_time - $start_time,
    ];
  }

  /**
   * Get WebhookEvent(s) with event_ids higher than the provided last_event_id.
   *
   * @param int $last_event_id
   *   Last known event id.
   *
   * @return \Drupal\opencollective_webhooks\Entity\WebhookEvent[]
   *   Found entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getEventsSince(int $last_event_id): array {
    $storage = $this->entityTypeManager()->getStorage('opencollective_webhook_event');
    $found = $storage->getQuery()
      ->condition('event_id', $last_event_id, '>')
      ->execute();

    return $storage->loadMultiple($found);
  }

  /**
   * Get the most recent event_id on the db table.
   *
   * @return int
   *   Most recent event_id.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getLastEventId(): int {
    $storage = $this->entityTypeManager()->getStorage('opencollective_webhook_event');
    $last = $storage->getQuery()
      ->range(0, 1)
      ->sort('event_id', 'DESC')
      ->execute();

    return $last ? (int) reset($last) : 0;
  }

}
