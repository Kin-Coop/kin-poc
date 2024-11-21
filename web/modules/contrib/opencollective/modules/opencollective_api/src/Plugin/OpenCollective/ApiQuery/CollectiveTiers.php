<?php

namespace Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery;

use Drupal\opencollective_api\ApiQueryPluginBase;

/**
 * Plugin implementation of the opencollective_api_query.
 *
 * @OpenCollectiveApiQuery(
 *   id = "collective_tiers",
 *   label = @Translation("Collective Tiers"),
 *   description = @Translation("Get a Collective's Tiers."),
 * )
 */
class CollectiveTiers extends ApiQueryPluginBase {

  const PLUGIN_ID = 'collective_tiers';

  /**
   * {@inheritdoc}
   */
  public function queryTemplate(): string {
    return <<<QUERY
      {
        collective(slug: "{{ collective_slug }}") {
          tiers(limit: {{ tiers_limit }}) {
            nodes {
              id
              slug
              name
              {{ tier_properties }}
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
      'tiers_limit' => 100,
      'tier_properties' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processQueryTemplateVariables(array $variables): array {
    if (is_array($variables['tier_properties'])) {
      $variables['tier_properties'] = implode(' ', $variables['tier_properties']);
    }

    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function processResults(array $results): array {
    return $results['data']['collective']['tiers']['nodes'] ?? [];
  }

}
