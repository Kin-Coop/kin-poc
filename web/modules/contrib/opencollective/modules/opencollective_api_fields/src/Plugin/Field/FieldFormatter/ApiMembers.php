<?php

namespace Drupal\opencollective_api_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\opencollective_api_fields\Plugin\Field\ApiFieldFormatterBase;

/**
 * Plugin implementation of the 'Api Members' formatter.
 *
 * @FieldFormatter(
 *   id = "opencollective_api_members",
 *   label = @Translation("Open Collective - Api Members"),
 *   field_types = {
 *     "opencollective_slug"
 *   }
 * )
 */
class ApiMembers extends ApiFieldFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'members_role' => 'BACKER',
      'members_limit' => 100,
      'member_properties' => 'id slug name imageUrl',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['members_role'] = [
      '#type' => 'select',
      '#title' => $this->t('Members Role'),
      '#description' => $this->t('Limit the shown members by a specific role.'),
      '#default_value' => $this->getSetting('members_role'),
      '#options' => [
        'BACKER' => $this->t('Backer'),
      ],
    ];
    $elements['members_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#description' => $this->t('Number of results to show.'),
      '#default_value' => $this->getSetting('members_limit'),
    ];
    $elements['member_properties'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Member Properties'),
      '#description' => $this->t('List the properties desired, separated by spaces.'),
      '#default_value' => $this->getSetting('member_properties'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Role: @members_role', ['@members_role' => $this->getSetting('members_role')]);
    $summary[] = $this->t('Limit: @members_limit', ['@members_limit' => $this->getSetting('members_limit')]);
    $summary[] = $this->t('Member Properties: @member_properties', ['@member_properties' => $this->getSetting('member_properties')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'opencollective_api_members',
        '#collective' => $item->value,
        '#members_limit' => $this->getSetting('members_limit'),
        '#members_role' => $this->getSetting('members_role'),
        '#member_properties' => $this->getSetting('member_properties'),
      ];
    }

    return $element;
  }

}
