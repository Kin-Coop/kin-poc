<?php

namespace Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery;

use Drupal\opencollective_api\ApiQueryPluginBase;

/**
 * Plugin implementation of the opencollective_api_query.
 *
 * @OpenCollectiveApiQuery(
 *   id = "collective_members",
 *   label = @Translation("Collective Members"),
 *   description = @Translation("Get a Collective's Members by role."),
 * )
 */
class CollectiveMembers extends ApiQueryPluginBase {

  const PLUGIN_ID = 'collective_members';

  /**
   * {@inheritdoc}
   */
  public function queryTemplate(): string {
    return <<<QUERY
      {
        account(slug: "{{ collective_slug }}") {
          members(role: {{ members_role }}, limit: {{ members_limit }}) {
            totalCount
            nodes {
              account {
                id
                slug
                name
                imageUrl
                {{ member_properties }}
              }
            }
          }
        }
      }
    QUERY;
  }

  /**
   * {@inheritdoc}
   */
  public function queryTemplateVariables(): array {
    return [
      'collective_slug' => NULL,
      'members_role' => 'BACKER',
      'members_limit' => 100,
      'member_properties' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processQueryTemplateVariables(array $variables): array {
    $variables['members_role'] = strtoupper($variables['members_role']);

    if (is_array($variables['member_properties'])) {
      $variables['member_properties'] = implode(' ', $variables['member_properties']);
    }

    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function processResults(array $results): array {
    return array_map(function (array $row) {
      return $row['account'];
    }, $results['data']['account']['members']['nodes'] ?? []);
  }

}
