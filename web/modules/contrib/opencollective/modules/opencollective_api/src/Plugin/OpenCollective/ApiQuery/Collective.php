<?php

namespace Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery;

use Drupal\opencollective_api\ApiQueryPluginBase;

/**
 * Plugin implementation of the opencollective_api_query.
 *
 * @OpenCollectiveApiQuery(
 *   id = "collective",
 *   label = @Translation("Collective"),
 *   description = @Translation("Get a Collective's details."),
 * )
 */
class Collective extends ApiQueryPluginBase {

  const PLUGIN_ID = 'collective';

  /**
   * {@inheritdoc}
   */
  public function queryTemplate(): string {
    return <<<QUERY
      {
        collective(slug: "{{ collective_slug }}") {
          id
          slug
          name
          type
          description
          {{ collective_properties }}
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
      'collective_properties' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processQueryTemplateVariables(array $variables): array {
    if (is_array($variables['collective_properties'])) {
      $variables['collective_properties'] = implode(' ', $variables['collective_properties']);
    }

    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function processResults(array $results): array {
    return $results['data']['collective'] ?? [];
  }

}
