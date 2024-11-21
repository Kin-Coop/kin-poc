<?php

namespace Drupal\opencollective_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\opencollective_fields\Plugin\Field\FieldFormatterBase;

/**
 * Plugin implementation of the 'EmbedBanner' formatter.
 *
 * @FieldFormatter(
 *   id = "opencollective_banner",
 *   label = @Translation("Open Collective - Banner"),
 *   description = @Translation("Banner shows a list of collective supporters."),
 *   field_types = {
 *     "opencollective_slug"
 *   }
 * )
 */
class Banner extends FieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'opencollective_banner',
        '#collective' => $item->value,
      ];
    }

    return $element;
  }

}
