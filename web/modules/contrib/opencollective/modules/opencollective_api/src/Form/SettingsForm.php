<?php

namespace Drupal\opencollective_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Configure opencollective_api settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'opencollective_api_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['opencollective_api.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('opencollective_api.settings');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api Key'),
      '#description' => $this->t('Obtaining an API Key is documented by Open Collective here: @doc_link', [
        '@doc_link' => Markup::create('<a href="https://graphql-docs-v2.opencollective.com/access">How to access the API</a>'),
      ]),
      '#default_value' => $config->get('api_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('opencollective_api.settings')
      ->set('api_key', trim($form_state->getValue('api_key')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
