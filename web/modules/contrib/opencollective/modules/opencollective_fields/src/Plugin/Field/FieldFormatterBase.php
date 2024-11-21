<?php

namespace Drupal\opencollective_fields\Plugin\Field;

use Drupal\Core\Field\FormatterBase;
use Drupal\opencollective\Service\Parameters;
use Drupal\opencollective\Service\ParametersRenderer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base for shared dependencies in field formatters.
 */
abstract class FieldFormatterBase extends FormatterBase {

  /**
   * Embed parameters.
   *
   * @var \Drupal\opencollective\Service\Parameters
   */
  protected Parameters $openCollectiveParameters;

  /**
   * Parameter renderer.
   *
   * @var \Drupal\opencollective\Service\ParametersRenderer
   */
  protected ParametersRenderer $parametersRenderer;

  /**
   * Construct().
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $configuration
   *   The plugin configuration.
   * @param \Drupal\opencollective\Service\Parameters $openCollectiveParameters
   *   Embed parameters.
   * @param \Drupal\opencollective\Service\ParametersRenderer $parametersRenderer
   *   Parameter renderer.
   */
  public function __construct($plugin_id, $plugin_definition, array $configuration, Parameters $openCollectiveParameters, ParametersRenderer $parametersRenderer) {
    $this->openCollectiveParameters = $openCollectiveParameters;
    $this->parametersRenderer = $parametersRenderer;

    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings']
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration,
      $container->get('opencollective.parameters'),
      $container->get('opencollective.parameters_renderer')
    );
  }

}
