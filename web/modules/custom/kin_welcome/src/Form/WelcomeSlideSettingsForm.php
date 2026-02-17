<?php

namespace Drupal\kin_welcome\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for welcome modal slides.
 */
class WelcomeSlideSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'kin_welcome_slide_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['kin_welcome.slides'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('kin_welcome.slides');
    $slides = $config->get('slides') ?? [];

    $form['#tree'] = TRUE;

    $form['intro'] = [
      '#markup' => '<p>' . $this->t('Edit the content for each slide in the welcome modal. The last slide should include a link to the next form.') . '</p>',
    ];

    // Build a fieldset for each slide
    $slide_keys = ['slide_1', 'slide_2', 'slide_3', 'slide_4'];

    foreach ($slide_keys as $index => $key) {
      $slide_number = $index + 1;
      $slide_data = $slides[$key] ?? [];

      $form['slides'][$key] = [
        '#type' => 'details',
        '#title' => $this->t('Slide @number', ['@number' => $slide_number]),
        '#open' => $index === 0,
      ];

      $form['slides'][$key]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#default_value' => $slide_data['title'] ?? '',
        '#required' => TRUE,
      ];

      $form['slides'][$key]['body'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Body'),
        '#default_value' => $slide_data['body'] ?? '',
        '#format' => $slide_data['body_format'] ?? 'basic_html',
        '#required' => TRUE,
      ];

      // Only show link fields on the last slide
      if ($key === 'slide_4') {
        $form['slides'][$key]['link_text'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Link Text'),
          '#default_value' => $slide_data['link_text'] ?? '',
          '#description' => $this->t('The text for the link on the last slide.'),
        ];

        $form['slides'][$key]['link_url'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Link URL'),
          '#default_value' => $slide_data['link_url'] ?? '',
          '#description' => $this->t('The URL the link should point to on the last slide.'),
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('kin_welcome.slides');
    $slides_values = $form_state->getValue('slides');

    $slides = [];
    foreach ($slides_values as $key => $slide) {
      $slides[$key] = [
        'title' => $slide['title'],
        'body' => $slide['body']['value'],
        'body_format' => $slide['body']['format'],
      ];

      // Save link fields if present (last slide)
      if (isset($slide['link_text'])) {
        $slides[$key]['link_text'] = $slide['link_text'];
        $slides[$key]['link_url'] = $slide['link_url'];
      }
    }

    $config->set('slides', $slides)->save();

    parent::submitForm($form, $form_state);
  }

}
