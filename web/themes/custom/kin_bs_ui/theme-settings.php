<?php

/**
 * @file
 * Functions to support kin_bs_ui theme settings.
 */

declare(strict_types=1);

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function kin_bs_ui_form_system_theme_settings_alter(array &$form, FormStateInterface $form_state): void {
  // Add this theme CSS into the options.
  if (isset($form['ui_suite_bootstrap']['library']['css_loading']['#options'])) {
    $form['ui_suite_bootstrap']['library']['css_loading']['#options']['kin_bs_ui/framework'] = \t('Starterkit');
  }
}
