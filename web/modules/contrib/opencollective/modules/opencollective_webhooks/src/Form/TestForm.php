<?php

namespace Drupal\opencollective_webhooks\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\opencollective_webhooks\Event\WebhookEvents;

/**
 * Provides a Open Collective - Incoming Webhooks form.
 */
class TestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'opencollective_webhooks_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('opencollective_webhooks.settings');
    $form['#attached']['library'][] = 'opencollective_webhooks/webhook-events-test-form';
    $form['#attached']['drupalSettings']['openCollectiveWebhooks']['testForm']['secret'] = $config->get('incoming_webhook_secret');

    $payloads = $this->getPayloads();
    $formatted_payloads = [
      '#type' => 'container',
    ];

    foreach ($payloads as $event_type => $details) {
      $formatted_payloads[str_replace('.', '-', $event_type)] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['opencollective-webhooks-sample-payload--wrapper'],
        ],
        'pre' => [
          '#type' => 'html_tag',
          '#tag' => 'pre',
          '#value' => Markup::create(\json_encode($details['payload'], JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)),
          '#attributes' => [
            'class' => ['opencollective-test-form'],
          ],
        ],
        '#states' => [
          'visible' => [
            'select[name="payload"]' => ['value' => $event_type],
          ],
        ],
      ];
    }

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'webhooks-events-test-form--wrapper',
        ],
      ],
      'form' => [
        '#type' => 'container',
        'payload' => [
          '#type' => 'select',
          '#title' => $this->t('Choose the payload to test.'),
          '#options' => array_combine(array_keys($payloads), array_keys($payloads)),
        ],
        'submit-ajax' => [
          '#type' => 'button',
          '#value' => $this->t('Send Webhook'),
        ],
        'formatted_payloads' => $formatted_payloads,
      ],
      'polling' => [
        '#type' => 'container',
        'polling_indicator' => [
          '#theme' => 'opencollective_webhooks_polling_indicator',
          '#event_name' => WebhookEvents::TRANSACTION_CREATED,
          '#event_data_expected' => [
            'transaction' => [
              'kind' => 'CONTRIBUTION',
            ],
          ],
        ],
        'polling_script' => [
          '#theme' => 'opencollective_webhooks_polling_script',
        ],
        'polling_output' => [
          '#type' => 'container',
          '#attributes' => [
            'id' => 'webhook-events-polling-output',
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

  /**
   * Get all example payload json files.
   *
   * @return array[]
   *   Array of example payload files details.
   */
  private function getPayloads(): array {
    static $payloads = [];
    if (!empty($payloads)) {
      return $payloads;
    }

    $payloads = [];
    $dir = realpath(__DIR__ . '/../../docs/sample-payloads');
    foreach (glob("{$dir}/*.json") as $absolute) {
      $payload = [
        'absolute' => $absolute,
        'filename' => basename($absolute),
        'event_type' => basename($absolute, '.json'),
        'payload' => Json::decode(file_get_contents($absolute)) ?? [],
      ];

      $payloads[$payload['event_type']] = $payload;
    }

    return $payloads;
  }

}
