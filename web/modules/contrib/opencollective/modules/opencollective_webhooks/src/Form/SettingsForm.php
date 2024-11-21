<?php

namespace Drupal\opencollective_webhooks\Form;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\Config;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

/**
 * Configure Open Collective - Incoming Webhooks settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'opencollective_webhooks_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['opencollective_webhooks.settings'];
  }

  /**
   * Get module's config.
   *
   * @return \Drupal\Core\Config\Config
   *   Config object.
   */
  private function getConfig(): Config {
    return $this->config('opencollective_webhooks.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->getConfig();
    $form = parent::buildForm($form, $form_state);

    $webhooks_url = Url::fromRoute(
      'opencollective_webhooks.incoming_webhook',
      ['incoming_webhook_secret' => $config->get('incoming_webhook_secret') ?? ''],
      ['absolute' => TRUE, 'https' => TRUE]
    )->toString();

    $form['incoming_webhook_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Incoming Webhook Settings'),
      '#description' => $this->t('This is how Open Collective will send data to your website.'),
      '#description_display' => 'before',

      'incoming_webhook_secret' => [
        '#type' => 'textfield',
        '#title' => $this->t('Incoming Webhook Secret'),
        '#description' => $this->t('Warning: Changing this will require updating your webhooks configuration on opencollective.com.'),
        '#default_value' => $config->get('incoming_webhook_secret'),
        '#required' => TRUE,
      ],

      'webhook_url' => [
        '#type' => 'container',
        'label' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => ['class' => ['form-item__label']],
          '#value' => $this->t('Incoming Webhook URL'),
        ],
        'url' => [
          '#markup' => Markup::create("<code style='background: #ededed; padding: 2px 4px;'>{$webhooks_url}</code>"),
        ],
        'description' => [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => ['class' => ['form-item__description']],
          '#value' => $this->t('Add this to Open Collective as a webhook for specific events, or for "All" events.'),
        ],
      ],
    ];

    $form['js_polling_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('JS Polling Settings'),
      '#description' => $this->t('These settings affect how the JavaScript Polling system works.'),
      '#description_display' => 'before',

      'poll_access_token_lifespan' => [
        '#type' => 'select',
        '#title' => $this->t('Polling Access Token Lifespan'),
        '#description' => $this->t('The total length of time a single poll can operate on a page.'),
        '#default_value' => $config->get('poll_access_token_lifespan'),
        '#options' => [
          300 => $this->t('5 Minutes'),
          600 => $this->t('10 Minutes'),
          900 => $this->t('15 Minutes'),
        ],
      ],

      'poll_length' => [
        '#type' => 'select',
        '#title' => $this->t('Polling Length'),
        '#description' => $this->t('The amount of time a single polling request will spend waiting on a response. While a poll is active it will use up one or two connections to your web server. Shorter length is best for small or shared servers. When disabled, the polling request will not wait any additional time for a response.'),
        '#default_value' => $config->get('poll_length'),
        '#options' => [
          0 => $this->t('Disabled'),
          2 => $this->t('2 Seconds'),
          5 => $this->t('5 Seconds'),
          10 => $this->t('10 Seconds'),
          15 => $this->t('15 Seconds'),
        ],
      ],
    ];

    $form['debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug Mode'),
      '#description' => $this->t('Log more messages in Drupal and browser consoles.'),
      '#default_value' => $config->get('debug_mode'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    switch (($form_state->getTriggeringElement()['#id'] ?? NULL)) {
      case 'edit-generate-secret':
        $this->getConfig()
          ->set('incoming_webhook_secret', Crypt::hashBase64(random_bytes(16)))
          ->save();

        $this->messenger()->addStatus('New secure secret key generated.');
        break;

      case 'edit-submit':
      default:
        $this->getConfig()
          ->set('incoming_webhook_secret', trim($form_state->getValue('incoming_webhook_secret')))
          ->set('poll_access_token_lifespan', $form_state->getValue('poll_access_token_lifespan'))
          ->set('poll_length', $form_state->getValue('poll_length'))
          ->set('debug_mode', $form_state->getValue('debug_mode'))
          ->save();
        break;
    }

    parent::submitForm($form, $form_state);
  }

}
