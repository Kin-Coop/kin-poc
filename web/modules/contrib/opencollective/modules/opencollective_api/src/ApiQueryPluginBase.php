<?php

namespace Drupal\opencollective_api;

use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for opencollective_api_query plugins.
 */
abstract class ApiQueryPluginBase extends PluginBase implements ApiQueryInterface {

  /**
   * ApiQueryPluginBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function description(): string {
    return (string) $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  abstract public function queryTemplate(): string;

  /**
   * {@inheritdoc}
   */
  public function queryTemplateVariables(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function processQueryTemplateVariables(array $variables): array {
    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function processResults(array $results): array {
    return $results;
  }

}
