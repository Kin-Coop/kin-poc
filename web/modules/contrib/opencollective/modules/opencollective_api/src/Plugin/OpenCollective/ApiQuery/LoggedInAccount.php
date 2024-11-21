<?php

namespace Drupal\opencollective_api\Plugin\OpenCollective\ApiQuery;

use Drupal\opencollective_api\ApiQueryPluginBase;

/**
 * Plugin implementation of the opencollective_api_query.
 *
 * @OpenCollectiveApiQuery(
 *   id = "logged_in_account",
 *   label = @Translation("Logged In Account"),
 *   description = @Translation("Get the logged in account details."),
 * )
 */
class LoggedInAccount extends ApiQueryPluginBase {

  const PLUGIN_ID = 'logged_in_account';

  /**
   * {@inheritdoc}
   */
  public function queryTemplate(): string {
    return <<<QUERY
      {
        loggedInAccount {
          id
          slug
          name
          {{ account_properties }}
        }
      }
    QUERY;
  }

  /**
   * {@inheritdoc}
   */
  public function queryTemplateVariables(): array {
    return [
      'account_properties' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function processQueryTemplateVariables(array $variables): array {
    if (is_array($variables['account_properties'])) {
      $variables['account_properties'] = implode(' ', $variables['account_properties']);
    }

    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function processResults(array $results): array {
    return $results['data']['loggedInAccount'] ?? [];
  }

}
