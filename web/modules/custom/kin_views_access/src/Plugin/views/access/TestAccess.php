<?php

  namespace Drupal\kin_views_access\Plugin\views\access;

  use Drupal\Core\Form\FormStateInterface;
  use Drupal\views\Plugin\views\access\AccessPluginBase;
  use Drupal\views\ViewExecutable;
  use Drupal\views\Plugin\views\display\DisplayPluginBase;

  /**
   * @ViewsAccess(
   *   id = "test_access",
   *   title = @Translation("Test Access"),
   *   help = @Translation("Custom access plugin with a test options form.")
   * )
   */
  class TestAccess extends AccessPluginBase {

    /**
     * {@inheritdoc}
     */
    public function access($account) {
      // For now always allow access.
      return TRUE;
    }

    /**
     * {@inheritdoc}
     */
    public function summaryTitle() {
      return $this->t('Always allowed (test)');
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(&$form, FormStateInterface $form_state) {
      parent::buildOptionsForm($form, $form_state);
      $form['test_message'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Test message'),
        '#default_value' => $this->options['test_message'] ?? '',
        '#description' => $this->t('Enter a test message just to prove options form works.'),
      ];
    }

    /**
     * {@inheritdoc}
     */
    public function defineOptions() {
      $options = parent::defineOptions();
      $options['test_message'] = ['default' => 'Hello world!'];
      return $options;
    }
  }
