<?php

namespace Drupal\opencollective_api_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opencollective_api_fields\Plugin\Field\ApiFieldFormatterBase;

/**
 * Plugin implementation of the 'Api Events' formatter.
 *
 * @FieldFormatter(
 *   id = "opencollective_api_events",
 *   label = @Translation("Open Collective - Api Events"),
 *   field_types = {
 *     "opencollective_slug"
 *   }
 * )
 */
class ApiEvents extends ApiFieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'event_properties' => 'id slug name imageUrl backgroundImageUrl',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['event_properties'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Event Properties'),
      '#description' => $this->t('List the properties desired, separated by spaces.'),
      '#default_value' => $this->getSetting('event_properties'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Event Properties: @event_properties', ['@event_properties' => $this->getSetting('event_properties')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'opencollective_api_events',
        '#collective' => $item->value,
        '#event_properties' => $this->getSetting('event_properties'),
      ];
    }

    return $element;
  }

}
