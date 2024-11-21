<?php

namespace Drupal\opencollective_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opencollective_fields\Plugin\Field\FieldFormatterBase;

/**
 * Plugin implementation of the 'EmbedButtons' formatter.
 *
 * @FieldFormatter(
 *   id = "opencollective_button",
 *   label = @Translation("Open Collective - Button"),
 *   field_types = {
 *     "opencollective_slug"
 *   }
 * )
 */
class Button extends FieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'color' => 'blue',
      'verb' => 'contribute',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements['color'] = [
      '#type' => 'select',
      '#title' => $this->t('Color'),
      '#description' => $this->t('Button color.'),
      '#default_value' => $this->getSetting('color'),
      '#options' => $this->openCollectiveParameters->getParameterOptions()->embedButtonColors(),
    ];
    $elements['verb'] = [
      '#type' => 'select',
      '#title' => $this->t('Verb'),
      '#description' => $this->t('Button first word.'),
      '#default_value' => $this->getSetting('verb'),
      '#options' => $this->openCollectiveParameters->getParameterOptions()->embedButtonVerbs(),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Color: @color', ['@color' => $this->getSetting('color')]);
    $summary[] = $this->t('Verb: @verb', ['@verb' => $this->getSetting('verb')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'opencollective_button',
        '#collective' => $item->value,
        '#color' => $this->getSetting('color'),
        '#verb' => $this->getSetting('verb'),
      ];
    }

    return $element;
  }

}
