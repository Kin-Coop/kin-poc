<?php

/**
 * @file
 * Theme prepreocess implementations.
 */

use Drupal\Component\Serialization\Json;
use Drupal\Core\Render\Element;

/**
 * Implements hook_theme().
 */
function opencollective_webhooks_theme($existing, $type, $theme, $path) {
  return [
    'opencollective_webhooks_polling_script' => [
      'variables' => [
        'access_token' => NULL,
        'last_event_id' => NULL,
      ],
    ],
    'opencollective_webhooks_polling_indicator' => [
      'variables' => [
        'event_name' => NULL,
        'event_data_expected' => [],
        'icon_active' => '/core/misc/icons/bebebe/cog.svg',
        'icon_success' => '/core/misc/icons/73b355/check.svg',
        'icon_error' => '/core/misc/icons/e32700/error.svg',
      ],
    ],
    'opencollective_webhook_event' => [
      'render element' => 'elements',
    ],
  ];
}

/**
 * Implements template_preprocess_hook().
 */
function template_preprocess_opencollective_webhooks_polling_script(&$variables) {
  /**
   * @var \Drupal\opencollective_webhooks\Service\PollingTokenManagerInterface $tokenManager
   */
  $config = \Drupal::config('opencollective_webhooks.settings');
  $tokenManager = \Drupal::service('opencollective_webhooks.polling_token_manager');
  $eventStorage = \Drupal::entityTypeManager()->getStorage('opencollective_webhook_event');
  $last_event_id = $eventStorage->getQuery()
    ->range(0, 1)
    ->sort('event_id', 'DESC')
    ->execute();
  $last_event_id = $last_event_id ? (int) reset($last_event_id) : 0;

  $token = $tokenManager->generateNewToken();
  $variables['attributes']['id'] = 'opencollective-webhooks-poll--' . $token;
  $variables['attributes']['class'] = ['opencollective-webhooks-poll'];

  $variables['#attached']['library'][] = 'opencollective_webhooks/js-events-polling';
  $variables['#attached']['drupalSettings']['openCollectiveWebhooks']['settings']['pollAccessTokenLifespan'] = $config->get('poll_access_token_lifespan');
  $variables['#attached']['drupalSettings']['openCollectiveWebhooks']['settings']['pollLength'] = $config->get('poll_length');
  $variables['#attached']['drupalSettings']['openCollectiveWebhooks']['settings']['debugMode'] = $config->get('debug_mode');
  $variables['#attached']['drupalSettings']['openCollectiveWebhooks']['polls'][$token] = [
    // 'active' => TRUE,
    'accessToken' => $token,
    'lastEventId' => $last_event_id,
  ];
}

/**
 * Implements template_preprocess_hook().
 */
function template_preprocess_opencollective_webhooks_polling_indicator(&$variables) {
  $variables['polling_script'] = [
    '#theme' => 'opencollective_webhooks_polling_script',
  ];

  $variables['content'] = [
    '#type' => 'container',
    '#attributes' => [
      'class' => [
        'opencollective-webhooks-polling-indicator',
      ],
      'data-polling-event-name' => $variables['event_name'],
      'data-polling-event-data-expected' => Json::encode($variables['event_data_expected']),
    ],
    '#attached' => [
      'library' => [
        'opencollective_webhooks/polling-indicator',
      ],
    ],
  ];
  $variables['content']['image_active'] = [
    '#type' => 'html_tag',
    '#tag' => 'img',
    '#attributes' => [
      'src' => $variables['icon_active'],
      'alt' => 'Indicates that polling is active.',
      'class' => [
        'opencollective-webhooks-polling-indicator--icon',
        'polling-active',
        'spinning',
      ],
    ],
  ];
  $variables['content']['image_success'] = [
    '#type' => 'html_tag',
    '#tag' => 'img',
    '#attributes' => [
      'src' => $variables['icon_success'],
      'alt' => 'Indicates that polling was successful.',
      'class' => [
        'opencollective-webhooks-polling-indicator--icon',
        'polling-success',
      ],
    ],
  ];
  $variables['content']['image_error'] = [
    '#type' => 'html_tag',
    '#tag' => 'img',
    '#attributes' => [
      'src' => $variables['icon_error'],
      'alt' => 'Indicates that polling experienced an error.',
      'class' => [
        'opencollective-webhooks-polling-indicator--icon',
        'polling-error',
      ],
    ],
  ];
}

/**
 * Prepares variables for webhook event templates.
 *
 * Default template: opencollective-webhook-event.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the webhook event information
 *     and any fields attached to the entity.
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_opencollective_webhook_event(array &$variables) {
  $show_raw = $variables['elements']['#view_mode'] === 'full';

  /** @var \Drupal\opencollective_webhooks\Entity\WebhookEventInterface $entity */
  $entity = $variables['elements']['#opencollective_webhook_event'];
  $variables['elements']['payload'] = [
    '#theme' => 'opencollective_debug',
    '#title' => t('Payload'),
    '#open' => FALSE,
    '#debug' => $entity->payload()->getPayload(),
    '#show_raw' => $show_raw,
  ];
  $variables['elements']['context'] = [
    '#theme' => 'opencollective_debug',
    '#title' => t('Context'),
    '#open' => FALSE,
    '#debug' => $entity->eventContext(),
    '#show_raw' => $show_raw,
  ];

  $variables['view_mode'] = $variables['elements']['#view_mode'];
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
