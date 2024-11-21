<?php

namespace Drupal\opencollective_api\Service;

use Drupal\opencollective_api\ApiQueryInterface;

/**
 * Contract for opencollective_api.client(s).
 */
interface ApiClientInterface {

  /**
   * Whether the client is ready to perform requests.
   *
   * @return bool
   *   True if client is ready, otherwise false.
   */
  public function isReady(): bool;

  /**
   * Perform a raw query.
   *
   * @param string $rendered_query
   *   Complete graphql query string.
   *
   * @return array
   *   Raw query results.
   */
  public function request(string $rendered_query): array;

  /**
   * Run any GraphQL Query.
   *
   * @param \Drupal\opencollective_api\ApiQueryInterface $query
   *   Query object.
   *
   * @return array
   *   Resulting data.
   */
  public function performQuery(ApiQueryInterface $query): array;

  /**
   * Get the query plugin manager service.
   *
   * @return ApiQueryPluginManager
   *   Plugin manager service.
   */
  public function queryPluginManager(): ApiQueryPluginManager;

}
