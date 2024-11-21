<?php

namespace Drupal\opencollective_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opencollective_fields\Plugin\Field\FieldFormatterBase;

/**
 * Plugin implementation of the 'EmbedBanner' formatter.
 *
 * @FieldFormatter(
 *   id = "opencollective_badge",
 *   label = @Translation("Open Collective - Badge"),
 *   description = @Translation("Badge shows a number of members a specific role has."),
 *   field_types = {
 *     "opencollective_slug"
 *   }
 * )
 */
class Badge extends FieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'members_role' => 'backers',
      'color' => NULL,
      'label' => NULL,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    // @todo this should be a select field of valid values.
    $elements['members_role'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Members Role'),
      '#description' => $this->t('Plural lowercase name of the role the badge represents.'),
      '#default_value' => $this->getSetting('members_role'),
      '#required' => TRUE,
    ];
    $elements['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('Text within the badge. Defaults to the name of the members role.'),
      '#default_value' => $this->getSetting('label'),
    ];
    $elements['color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Color'),
      '#description' => $this->t('The background color for the number of members in the badge.'),
      '#default_value' => $this->getSetting('color'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Members role: @members_role', ['@members_role' => $this->getSetting('members_role')]);
    $summary[] = $this->t('Label: @label', ['@label' => $this->getSetting('label')]);
    $summary[] = $this->t('Color: @color', ['@color' => $this->getSetting('color')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'opencollective_badge',
        '#collective' => $item->value,
        // '#tier' => NULL,
        '#members_role' => $this->getSetting('members_role'),
        '#label' => $this->getSetting('label'),
        '#color' => $this->getSetting('color'),
      ];
    }

    return $element;
  }

}
