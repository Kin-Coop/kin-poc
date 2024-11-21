<?php

namespace Drupal\opencollective\Service;

/**
 * Converts Parameters (not ParameterOptions) into Drupal form fields.
 */
class ParametersRenderer {

  /**
   * Convert a contribution flow URL parameter array into a Drupal field.
   *
   * @param array $parameter
   *   Item from OpenCollectiveParameters::contributionFlowUrlParameters.
   * @param mixed|null $default_value
   *   Default value for the field.
   *
   * @return array
   *   Drupal field render array.
   */
  public function renderAsField(array $parameter, $default_value = NULL): array {
    $field = [
      '#title' => $parameter['label'],
      '#description' => $parameter['description'],
      '#default_value' => $default_value ?? $parameter['default'],
      '#attributes' => [
        'class' => [
          'oc-contrib-flow-url-param',
          'oc-contrib-flow-url-param--' . $parameter['type'],
        ],
      ],
    ];

    switch ($parameter['type']) {
      case 'decimal':
        $field['#type'] = 'number';
        $field['#attributes']['#step'] = '0.01';
        break;

      case 'integer':
        $field['#type'] = 'number';
        $field['#attributes']['#step'] = '1';
        break;

      case 'float':
        $field['#type'] = 'number';
        $field['#attributes']['#step'] = '0.00001';
        break;

      case 'string':
        $field['#type'] = 'textfield';
        if (is_array($parameter['options'])) {
          $field['#type'] = 'select';
          $field['#options'] = $parameter['options'];
        }
        break;

      case 'boolean':
        $field['#type'] = 'checkbox';
        break;

      case 'array':
        if (is_array($parameter['options'])) {
          $field['#type'] = 'checkboxes';
          $field['#options'] = $parameter['options'];
        }
        break;
    }

    return $field;
  }

}
