<?php

namespace Drupal\opencollective_funding\Plugin\Funding;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\funding\Plugin\Funding\FundingProviderBase as FundingFundingProviderBase;
use Drupal\opencollective\Service\Parameters;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for open collective funding providers.
 */
abstract class FundingProviderBase extends FundingFundingProviderBase implements ContainerFactoryPluginInterface {

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
   * @param \Drupal\opencollective\Service\Parameters $openCollectiveParameters
   *   Embed parameters.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Parameters $openCollectiveParameters
  ) {
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
