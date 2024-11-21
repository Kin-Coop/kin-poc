<?php

namespace Drupal\opencollective_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opencollective_fields\Plugin\Field\FieldFormatterBase;

/**
 * Plugin implementation of the 'EmbedBanner' formatter.
 *
 * @FieldFormatter(
 *   id = "opencollective_contributors_image",
 *   label = @Translation("Open Collective - Contributors Image"),
 *   description = @Translation("Image shows avatars of members of a specific role. Optionally shows a call to action button."),
 *   field_types = {
 *     "opencollective_slug"
 *   }
 * )
 */
class ContributorsImage extends FieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'members_role' => 'backers',
      'query' => [],
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
    $elements['query'] = [
      '#type' => 'details',
      '#title' => $this->t('Query'),
      '#description' => $this->t('Additional query parameters.'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];

    $parameters = $this->openCollectiveParameters->contributorsImageUrlParameters();
    foreach ($parameters as $name => $parameter) {
      $elements['query'][$name] = $this->parametersRenderer->renderAsField($parameter, $this->getSetting($name));
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Members role: @members_role', ['@members_role' => $this->getSetting('members_role')]);
    $summary[] = $this->t('Query: @query', ['@query' => http_build_query($this->getSetting('query'))]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'opencollective_contributors_image',
        '#collective' => $item->value,
        // '#tier' => NULL,
        '#members_role' => $this->getSetting('members_role'),
        '#query' => $this->getSetting('query') ?? [],
      ];
    }

    return $element;
  }

}
