<?php

namespace Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery;

use Drupal\opencollective_api\ApiQueryPluginBase;

/**
 * Plugin implementation of the opencollective_api_query.
 *
 * @OpenCollectiveApiQuery(
 *   id = "collective_event_tiers",
 *   label = @Translation("Collective Event Tiers"),
 *   description = @Translation("Get list of tiers for a collective event."),
 * )
 */
class CollectiveEventTiers extends ApiQueryPluginBase {

  const PLUGIN_ID = 'collective_event_tiers';

  /**
   * {@inheritdoc}
   */
  public function queryTemplate(): string {
    return <<<QUERY
      {
        event(slug: "{{ event_slug }}") {
          tiers {
            nodes {
              amount {
                currency
                value
                valueInCents
              }
              amountType
              availableQuantity
              button description
              endsAt
              frequency
              goal {
                currency
                value
                valueInCents
              }
              id
              interval
              legacyId
              maxQuantity
              minimumAmount {
                currency
                value
                valueInCents
              }
              name
              presets
              singleTicket
              slug
              type
              useStandalonePage
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
      'event_slug' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processResults(array $results): array {
    return $results['data']['event']['tiers']['nodes'] ?? [];
  }

}
