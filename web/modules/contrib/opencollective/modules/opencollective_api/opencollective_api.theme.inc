<?php

/**
 * @file
 * Open Collective Api Fields theme implementations.
 */

use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\CollectiveEvents;
use Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery\CollectiveMembers;

/**
 * Implements hook_theme().
 */
function opencollective_api_theme($existing, $type, $theme, $path) {
  return [
    'opencollective_api_events' => [
      'variables' => [
        'collective' => NULL,
        'events' => [],
      ],
    ],
    'opencollective_api_members' => [
      'variables' => [
        'collective' => NULL,
        'members_role' => 'BACKER',
        'members_limit' => 20,
        'member_properties' => 'id slug name imageUrl',
        'members' => [],
      ],
    ],
  ];
}

/**
 * Implements template_preprocess_HOOK().
 */
function template_preprocess_opencollective_api_members(&$variables) {
  $variables['#attached']['library'][] = 'opencollective_api/api-members';

  /**
   * @var \Drupal\opencollective_api\Service\ApiClientInterface $client
   * @var \Drupal\opencollective\Service\Parameters $openCollectiveParameters
   */
  $client = \Drupal::service('opencollective_api.client');
  $openCollectiveParameters = \Drupal::service('opencollective.parameters');
  $query = $client->queryPluginManager()->createInstance(CollectiveMembers::PLUGIN_ID);

  // Validate the members_role.
  $members_role = strtoupper($variables['members_role']);
  if (!$openCollectiveParameters->keyExists($members_role, $openCollectiveParameters->getParameterOptions()->memberRoles())) {
    $members_role = 'BACKER';
  }

  $variables['members'] = $client->performQuery($query, [
    'collective_slug' => $variables['collective'],
    'members_limit' => $variables['members_limit'] ?? 20,
    'members_role' => $members_role,
    'member_properties' => $variables['member_properties'],
  ]);
}

/**
 * Implements template_preprocess_HOOK().
 */
function template_preprocess_opencollective_api_events(&$variables) {
  $variables['#attached']['library'][] = 'opencollective_api/api-events';

  /** @var \Drupal\opencollective_api\Service\ApiClientInterface $client */
  $client = \Drupal::service('opencollective_api.client');
  $query = $client->queryPluginManager()->createInstance(CollectiveEvents::PLUGIN_ID);

  $variables['events'] = $client->performQuery($query, [
    'collective_slug' => $variables['collective'],
  ]);
}
