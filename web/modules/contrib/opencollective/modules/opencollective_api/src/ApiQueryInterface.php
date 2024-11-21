<?php

namespace Drupal\opencollective_api;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Interface for opencollective_api_query plugins.
 */
interface ApiQueryInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Returns the plugin id.
   *
   * @return string
   *   The plugin id.
   */
  public function id(): string;

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label(): string;

  /**
   * Returns the translated plugin description.
   *
   * @return string
   *   The translated title.
   */
  public function description(): string;

  /**
   * Returns the GraphQL Query template string.
   *
   * Use twig syntax for variables/context replacements in the query string.
   *
   * @return string
   *   Query string.
   */
  public function queryTemplate(): string;

  /**
   * Associative array of variables to replace in the query template.
   *
   * Key should be the variable name, and value the variable value.
   *
   * @return array
   *   Variables.
   */
  public function queryTemplateVariables(): array;

  /**
   * Before sending query template variables to the query, alter the values.
   *
   * @param array $variables
   *   Query template variables.
   *
   * @return array
   *   Processed query template variables.
   */
  public function processQueryTemplateVariables(array $variables): array;

  /**
   * After the query returns a response, process the results.
   *
   * @param array $results
   *   Array of raw results from graphql response.
   *
   * @return array
   *   Processed/massaged results.
   */
  public function processResults(array $results): array;

}
