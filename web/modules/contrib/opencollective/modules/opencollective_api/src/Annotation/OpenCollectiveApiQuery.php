<?php

namespace Drupal\opencollective_api\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines opencollective_api_query annotation object.
 *
 * @Annotation
 */
class OpenCollectiveApiQuery extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The human-readable description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
