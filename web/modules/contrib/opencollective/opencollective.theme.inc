<?php

/**
 * @file
 * Theme related implementations.
 */

use Drupal\Component\Serialization\Json;
use Drupal\Core\Render\Markup;

/**
 * Implements hook_theme().
 */
function opencollective_theme() {
  return [
    'opencollective_badge' => [
      'file' => 'opencollective.theme.inc',
      'variables' => [
        'collective' => NULL,
        // 'tier' => NULL,
        'members_role' => NULL,
        'label' => NULL,
        'color' => NULL,
      ],
    ],
    'opencollective_contributors_image' => [
      'file' => 'opencollective.theme.inc',
      'variables' => [
        'collective' => NULL,
        'members_role' => 'backers',
        'query' => [
          'width' => NULL,
          'height' => NULL,
          'limit' => NULL,
          'avatarHeight' => NULL,
          'button' => NULL,
          'format' => NULL,
        ],
      ],
    ],
    'opencollective_debug' => [
      'file' => 'opencollective.theme.inc',
      'variables' => [
        'title' => NULL,
        'open' => TRUE,
        'show_dump' => TRUE,
        'show_raw' => FALSE,
        'debug' => [],
      ],
    ],
    'opencollective_banner' => [
      'file' => 'opencollective.theme.inc',
      'variables' => [
        'collective' => NULL,
        'style' => [],
      ],
    ],
    'opencollective_button' => [
      'file' => 'opencollective.theme.inc',
      'variables' => [
        'collective' => NULL,
        'color' => 'blue',
        'verb' => 'donate',
      ],
    ],
    'opencollective_contribution_flow' => [
      'file' => 'opencollective.theme.inc',
      'variables' => [
        'collective' => NULL,
        'event' => NULL,
        'tier' => NULL,
        'query' => [],
      ],
    ],
  ];
}

/**
 * Implements template_preprocess_HOOK().
 *
 * @link https://docs.opencollective.com/help/collectives/collective-settings/data-export#badge
 */
function template_preprocess_opencollective_badge(&$variables) {
  $query = '';
  $query_array = array_filter([
    'label' => $variables['label'],
    'color' => $variables['color'],
  ]);

  if ($query_array) {
    $query = '?' . http_build_query($query_array);
  }

  $url = "https://opencollective.com/{$variables['collective']}/badge.svg";
  if ($variables['members_role']) {
    $url = "https://opencollective.com/{$variables['collective']}/{$variables['members_role']}/badge.svg";
  }

  $variables['attributes']['type'] = 'image/svg+xml';
  $variables['attributes']['data'] = "{$url}{$query}";
}

/**
 * Implements template_preprocess_HOOK().
 *
 * @link https://docs.opencollective.com/help/collectives/collective-settings/data-export#contributor-image
 */
function template_preprocess_opencollective_contributors_image(&$variables) {
  $query = '';
  $variables['query'] = array_filter($variables['query']);
  if ($variables['query'] && is_array($variables['query'])) {
    $query = '?' . http_build_query(array_filter($variables['query']));
  }

  $url = "https://opencollective.com/{$variables['collective']}/backers.svg";
  if ($variables['members_role'] ?? NULL) {
    // @todo can't prove this works.
    // $url = "https://opencollective.com/{$variables['collective']}/{$variables['members_role']}.svg";
  }

  $variables['attributes']['type'] = 'image/svg+xml';
  $variables['attributes']['data'] = "{$url}{$query}";
}

/**
 * Implements template_preprocess_HOOK().
 */
function template_preprocess_opencollective_debug(&$variables) {
  $show_dump = $variables['show_dump'] ?? TRUE;
  $show_raw = $variables['show_raw'] ?? FALSE;
  $raw = \json_encode($variables['debug'], JSON_PRETTY_PRINT);

  $variables['content'] = [
    '#type' => 'details',
    '#open' => $variables['open'],
    '#title' => $variables['title'] ?? t('Debug'),
    'debug' => [
      '#markup' => _opencollective_dump_as_markup($variables['debug']),
      '#access' => $show_dump,
    ],
    'raw' => [
      '#markup' => Markup::create("<pre>{$raw}</pre>"),
      '#access' => $show_raw,
    ],
  ];
}

/**
 * Implements template_preprocess_HOOK().
 *
 * @link https://docs.opencollective.com/help/collectives/widgets
 */
function template_preprocess_opencollective_banner(&$variables) {
  $query = '';
  if ($variables['style']) {
    $query = '?style=' . Json::encode($variables['style']);
  }

  $variables['attributes']['src'] = "https://opencollective.com/{$variables['collective']}/banner.js{$query}";
}

/**
 * Implements template_preprocess_HOOK().
 *
 * @link https://docs.opencollective.com/help/collectives/widgets
 */
function template_preprocess_opencollective_button(&$variables) {
  /** @var \Drupal\opencollective\Service\Parameters $openCollectiveParameters */
  $openCollectiveParameters = \Drupal::service('opencollective.parameters');

  $variables['color'] = $variables['color'] ?? $openCollectiveParameters->getDefault($openCollectiveParameters->getParameterOptions()->embedButtonColors());
  $variables['verb'] = $variables['verb'] ?? $openCollectiveParameters->getDefault($openCollectiveParameters->getParameterOptions()->embedButtonVerbs());

  // Enforce limited options.
  if (!$openCollectiveParameters->keyExists($variables['color'], $openCollectiveParameters->getParameterOptions()->embedButtonColors())) {
    $variables['color'] = $openCollectiveParameters->getDefault($openCollectiveParameters->getParameterOptions()->embedButtonColors());
  }
  if (!$openCollectiveParameters->keyExists($variables['verb'], $openCollectiveParameters->getParameterOptions()->embedButtonVerbs())) {
    $variables['verb'] = $openCollectiveParameters->getDefault($openCollectiveParameters->getParameterOptions()->embedButtonVerbs());
  }

  $variables['attributes']['src'] = "https://opencollective.com/{$variables['collective']}/{$variables['verb']}/button.js";
  $variables['attributes']['color'] = $variables['color'];
}

/**
 * Implements template_preprocess_HOOK().
 *
 * @link https://docs.opencollective.com/help/collectives/contribution-flow
 */
function template_preprocess_opencollective_contribution_flow(&$variables) {
  $query = '';
  if ($variables['query']) {
    $query = '?' . http_build_query($variables['query']);
  }

  $url = "https://opencollective.com/embed/{$variables['collective']}/donate";
  // Handle the url difference between default and specific tiers.
  if ($variables['tier']) {
    $url = "https://opencollective.com/embed/{$variables['collective']}/contribute/{$variables['tier']}";

    if ($variables['event']) {
      $url = "https://opencollective.com/embed/{$variables['collective']}/events/{$variables['event']}/contribute/{$variables['tier']}";
    }
  }

  if (!empty($variables['query']['shouldRedirectParent'])) {
    $variables['attributes']['sandbox'] = 'allow-scripts allow-top-navigation allow-same-origin';
  }

  $variables['attributes']['src'] = "{$url}{$query}";
  $variables['attributes']['style'] = 'width: 100%; min-height: 100vh;';
}
