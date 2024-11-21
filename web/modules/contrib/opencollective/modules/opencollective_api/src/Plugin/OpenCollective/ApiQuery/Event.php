<?php

namespace Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery;

use Drupal\opencollective_api\ApiQueryPluginBase;

/**
 * Plugin implementation of the opencollective_api_query.
 *
 * @OpenCollectiveApiQuery(
 *   id = "event",
 *   label = @Translation("Single Event"),
 *   description = @Translation("Get single event (there is no endpoint for getting multiple event objets)."),
 * )
 */
class Event extends ApiQueryPluginBase {

  const PLUGIN_ID = 'event';

  /**
   * {@inheritdoc}
   */
  public function queryTemplate(): string {
    return <<<QUERY
      {
        event(slug: "{{ event_slug }}") {
          id
          slug
          name
          description
          startsAt
          endsAt
          imageUrl
          backgroundImageUrl
          isActive
          isFrozen
          isHost
          isArchived
          tags
          socialLinks {
            type
            url
          }
          {{ event_properties }}
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
      'event_properties' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processResults(array $results): array {
    return $results['data']['event'] ?? [];
  }

}
