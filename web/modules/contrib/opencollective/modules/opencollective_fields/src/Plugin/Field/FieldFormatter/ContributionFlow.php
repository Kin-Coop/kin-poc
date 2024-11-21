<?php

namespace Drupal\opencollective_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opencollective_fields\Plugin\Field\FieldFormatterBase;

/**
 * Plugin implementation of the 'EmbedContributionFlow' formatter.
 *
 * @FieldFormatter(
 *   id = "opencollective_contribution_flow",
 *   label = @Translation("Open Collective - Contribution Flow"),
 *   field_types = {
 *     "opencollective_slug"
 *   }
 * )
 */
class ContributionFlow extends FieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'query' => [],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['query'] = [
      '#type' => 'details',
      '#title' => $this->t('Query'),
      '#description' => $this->t('Additional query parameters.'),
      '#open' => FALSE,
      '#tree' => TRUE,
    ];

    $parameters = $this->openCollectiveParameters->contributionFlowUrlParameters();
    foreach ($parameters as $name => $parameter) {
      $elements['query'][$name] = $this->parametersRenderer->renderAsField($parameter, $this->getSetting($name));
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Query: @query', ['@query' => http_build_query($this->getSetting('query'))]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Event and tier don't make sense in the context of an admin setting
      // up this field for multiple entities.
      $element[$delta] = [
        '#theme' => 'opencollective_contribution_flow',
        '#collective' => $item->value,
        '#query' => $this->getSetting('query') ?? [],
      ];
    }

    return $element;
  }

}
