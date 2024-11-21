<?php

namespace Drupal\opencollective_api\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Template\TwigEnvironment;
use Psr\Log\LoggerInterface;

/**
 *
 */
class ApiClientFactory {

  /**
   * Base uri for all clients.
   *
   * @const string
   */
  const BASE_URI = 'https://api.opencollective.com/graphql/';

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * HTTP Client factory.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  private ClientFactory $clientFactory;

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
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Config factory.
   * @param \Drupal\Core\Http\ClientFactory $clientFactory
   *   Http client factory.
   * @param \Drupal\Core\Template\TwigEnvironment $twig
   *   Twig.
   * @param \Drupal\opencollective_api\Service\ApiQueryPluginManager $queryPluginManager
   *   Plugin manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ClientFactory $clientFactory, TwigEnvironment $twig, ApiQueryPluginManager $queryPluginManager, LoggerInterface $logger) {
    $this->configFactory = $configFactory;
    $this->clientFactory = $clientFactory;
    $this->twig = $twig;
    $this->queryPluginManager = $queryPluginManager;
    $this->logger = $logger;
  }

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * Create headers array.
   *
   * @param array $headers
   *   Additional headers to be added to requests.
   *
   * @return string[]
   *   Headers array.
   */
  private function makeHeaders(array $headers = []): array {
    return [
      'Content-Type' => 'application/json',
    ] + $headers;
  }

  /**
   * Get an api client instance using global default configuration.
   *
   * @return \Drupal\opencollective_api\Service\ApiClientInterface
   *   Api Client instance.
   */
  public function create(): ApiClientInterface {
    $config = $this->configFactory->get('opencollective_api.settings');
    $client = $this->clientFactory->fromOptions([
      'base_uri' => static::BASE_URI,
      'headers' => $this->makeHeaders([
        'Api-Key' => $config->get('api_key'),
      ]),
    ]);
    return new ApiClient(
      $config,
      $client,
      $this->twig,
      $this->queryPluginManager,
      $this->logger
    );
  }

  /**
   * Create Api Client instance that authorizes using a Bearer access token.
   *
   * @param string $access_token
   *   Access token.
   *
   * @return \Drupal\opencollective_api\Service\ApiClientInterface
   *   Api Client.
   */
  public function createBearerClient(string $access_token): ApiClientInterface {
    $client = $this->clientFactory->fromOptions([
      'base_uri' => static::BASE_URI,
      'headers' => $this->makeHeaders([
        'Authorization' => 'Bearer ' . $access_token,
      ]),
    ]);
    return new ApiClient(
      $this->configFactory->get('opencollective_api.settings'),
      $client,
      $this->twig,
      $this->queryPluginManager,
      $this->logger
    );
  }

}
