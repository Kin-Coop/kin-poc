<?php

namespace Drupal\opencollective_api\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\opencollective_api\Exception\ApiResponseError;
use Drupal\opencollective_api\ApiQueryInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

/**
 * Drupal OpenCollective GraphQL Client adapter.
 *
 * @link https://github.com/mghoneimy/php-graphql-client
 */
class ApiClient implements ApiClientInterface {

  /**
   * Module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private ImmutableConfig $config;

  /**
   * Twig.
   *
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  private TwigEnvironment $twig;

  /**
   * Query plugin manager.
   *
   * @var ApiQueryPluginManager
   */
  private ApiQueryPluginManager $queryPluginManager;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * Client for open Collective graphQL.
   *
   * @var \GuzzleHttp\Client
   */
  private Client $client;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   Module config.
   * @param \GuzzleHttp\Client $client
   *   Guzzle client.
   * @param \Drupal\Core\Template\TwigEnvironment $twig
   *   Twig.
   * @param \Drupal\opencollective_api\Service\ApiQueryPluginManager $queryPluginManager
   *   Plugin manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(ImmutableConfig $config, Client $client, TwigEnvironment $twig, ApiQueryPluginManager $queryPluginManager, LoggerInterface $logger) {
    $this->config = $config;
    $this->client = $client;
    $this->twig = $twig;
    $this->queryPluginManager = $queryPluginManager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function isReady(): bool {
    $apikey = $this->config->get('api_key');
    return (
      is_string($apikey)
      &&
      // In my limited experience, keys seem to be 40 characters.
      (strlen($apikey) > 39)
    );
  }

  /**
   * Render the GraphQL query string from a OpenCollectiveApiQuery plugin.
   *
   * @param \Drupal\opencollective_api\ApiQueryInterface $query
   *   Query plugin.
   * @param array $variables
   *   Replacement variables.
   *
   * @return string
   *   Resulting GraphQL query string.
   */
  private function renderQuery(ApiQueryInterface $query, array $variables = []): string {
    // Override default variable values with request values.
    $variables = array_replace($query->queryTemplateVariables(), $variables);

    // Process variables according to the query plugin.
    $variables = $query->processQueryTemplateVariables($variables);

    // Render the query.
    return $this->twig->renderInline($query->queryTemplate(), $variables);
  }

  /**
   * {@inheritdoc}
   */
  public function request(string $rendered_query): array {
    $this->logger->debug('Performing query: @rendered_query', [
      '@rendered_query' => $rendered_query,
    ]);

    try {
      $response = $this->client->post('v2', [
        'json' => [
          'query' => $rendered_query,
        ],
      ]);

      if ($response->getStatusCode() === 200) {
        $results = Json::decode($response->getBody()->getContents());

        if ($results && isset($results['errors'])) {
          $error = array_shift($results['errors']);
          throw new ApiResponseError($this->templateApiResponseError($error));
        }

        return $results ?: [];
      }
    }
    catch (\Exception $exception) {
      $this->logger->error($exception->getMessage());
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function performQuery(ApiQueryInterface $query, array $variables = []): array {
    $rendered_query = $this->renderQuery($query, $variables);
    $results = $this->request($rendered_query);
    return $query->processResults($results);
  }

  /**
   * {@inheritdoc}
   */
  public function queryPluginManager(): ApiQueryPluginManager {
    return $this->queryPluginManager;
  }

  /**
   * Template the OC error response.
   *
   * @param array $error
   *   Error array.
   *
   * @return string
   *   Error string.
   */
  private function templateApiResponseError(array $error): string {
    return strtr('Message: @message. @locations_line @locations_column @path', [
      '@message' => $error['message'] ?? 'None',
      '@locations_line' => isset($error['locations'][0]['line']) ? 'Line: ' . $error['locations'][0]['line'] : '',
      '@locations_column' => isset($error['locations'][0]['column']) ? 'Column: ' . $error['locations'][0]['column'] : '',
      '@path' => isset($error['path'][0]) ? 'Path: ' . $error['path'][0] : '',
    ]);
  }

}
