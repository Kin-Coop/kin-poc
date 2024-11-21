<?php

namespace Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery;

use Drupal\opencollective_api\ApiQueryPluginBase;

/**
 * Plugin implementation of the opencollective_api_query.
 *
 * @OpenCollectiveApiQuery(
 *   id = "account",
 *   label = @Translation("Account"),
 *   description = @Translation("Get an Account's details."),
 * )
 */
class Account extends ApiQueryPluginBase {

  const PLUGIN_ID = 'account';

  /**
   * {@inheritdoc}
   */
  public function queryTemplate(): string {
    return <<<QUERY
      {
        account(slug: "{{ account_slug }}") {
          id
          slug
          emails
          name
        }
      }
    QUERY;
  }

  /**
   * {@inheritdoc}
   */
  public function queryTemplateVariables(): array {
    return [
      'account_slug' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processResults(array $results): array {
    return $results['data']['account'] ?? [];
  }

}
