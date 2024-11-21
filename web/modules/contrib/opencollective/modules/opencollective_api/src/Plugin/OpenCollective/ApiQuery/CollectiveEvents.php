<?php

namespace Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery;

use Drupal\opencollective_api\ApiQueryPluginBase;

/**
 * Plugin implementation of the opencollective_api_query.
 *
 * @OpenCollectiveApiQuery(
 *   id = "collective_events",
 *   label = @Translation("Collective Events"),
 *   description = @Translation("Get list of events for a collective."),
 * )
 */
class CollectiveEvents extends ApiQueryPluginBase {

  const PLUGIN_ID = 'collective_events';

  /**
   * {@inheritdoc}
   */
  public function queryTemplate(): string {
    return <<<QUERY
      {
        account(slug: "{{ collective_slug }}") {
          childrenAccounts(accountType: EVENT){
            nodes {
              backgroundImageUrl
              categories
              id
              imageUrl
              isActive
              location {
                address
                country
                id
                lat
                long
                name
              }
              longDescription
              name
              slug
              tags
              updatedAt
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
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processResults(array $results): array {
    return $results['data']['account']['childrenAccounts']['nodes'] ?? [];
  }

}
