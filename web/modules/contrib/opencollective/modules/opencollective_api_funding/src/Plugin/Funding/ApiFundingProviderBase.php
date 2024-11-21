<?php

namespace Drupal\opencollective_api_funding\Plugin\Funding;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\funding\Plugin\Funding\FundingProviderBase;
use Drupal\opencollective_api\Service\ApiClient;
use Drupal\opencollective\Service\Parameters;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for open collective funding providers.
 */
abstract class ApiFundingProviderBase extends FundingProviderBase implements ContainerFactoryPluginInterface {

  /**
   * Client.
   *
   * @var \Drupal\opencollective_api\Service\ApiClient
   */
  protected ApiClient $apiClient;

  /**
   * Embed parameters.
   *
   * @var \Drupal\opencollective\Service\Parameters
   */
  protected Parameters $openCollectiveParameters;

  /**
   * BlockWithDependencyInjection constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\opencollective_api\Service\ApiClient $apiClient
   *   Client.
   * @param \Drupal\opencollective\Service\Parameters $openCollectiveParameters
   *   Embed parameters.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ApiClient $apiClient,
    Parameters $openCollectiveParameters
  ) {
    $this->apiClient = $apiClient;
    $this->openCollectiveParameters = $openCollectiveParameters;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('opencollective_api.client'),
      $container->get('opencollective.parameters')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function validate($data): bool {
    $this->validateIsStringOrArray($data);

    if (is_array($data)) {
      $this->validateRequiredPropertyIsString($data, 'collective');
    }

    return TRUE;
  }

}
