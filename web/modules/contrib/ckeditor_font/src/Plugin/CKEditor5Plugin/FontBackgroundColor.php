<?php

declare(strict_types = 1);

namespace Drupal\ckeditor_font\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableTrait;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\ckeditor5\Plugin\CKEditor5PluginConfigurableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Language\LanguageInterface;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Font Size plugin.
 *
 * @internal
 *   Plugin classes are internal.
 */
class FontBackgroundColor extends CKEditor5PluginDefault implements CKEditor5PluginConfigurableInterface {

  use CKEditor5PluginConfigurableTrait;

  /**
   * {@inheritdoc}
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    // Get static config
    $font_all_options['fontBackgroundColor']['colors'] = $this->generateFontColorSetting($this->getConfiguration()['font_backgroundcolors']);
    $font_all_options['fontBackgroundColor']['columns'] = $this->getConfiguration()['columns'];
    $font_all_options['fontBackgroundColor']['documentColors'] = $this->getConfiguration()['documentColors'];

    
    // Convert config to ['title']['model'] sub-array
    return $font_all_options;

  }

  /**
   * {@inheritdoc}
   *
   * @see \Drupal\editor\Form\EditorImageDialog
   * @see editor_image_upload_settings_form()
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['font_backgroundcolors'] = array(
      '#title' => $this->t('Font background colors'),
      '#type' => 'textarea',
      '#default_value' => $this->configuration['font_backgroundcolors'],
      '#description' => $this->t('Enter colors on new lines. Colors must be added with the following syntax:<br><code>rgb(255,255,255)|Color<br>hsl(0,0%,0%)|Color<br>#000000|Color</code>'),
    );

    $form['columns'] = array(
      '#type' => 'number',
      '#title' => $this->t('Number of columns'),
      '#min' => '1',
      '#description' => $this->t("Represents the number of columns in the font color dropdown."),
      '#default_value' => $this->configuration['columns'] ? $this->configuration['columns'] : 5,
    );

    $form['documentColors'] = array(
      '#type' => 'number',
      '#title' => $this->t('Maximum available colors'),
      '#min' => '0',
      '#description' => $this->t("Determines the maximum number of available document colors. Setting it to 0 will disable the document colors feature."),
      '#default_value' => $this->configuration['documentColors'],
    );


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($this->generateFontColorSetting($form_state->getValue('font_backgroundcolors')) === FALSE) {
      $form_state->setError($form['font_backgroundcolors'], t('The provided list of font colors is syntactically incorrect.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['font_backgroundcolors'] = $form_state->getValue('font_backgroundcolors');
    $this->configuration['columns'] = (int)$form_state->getValue('columns');
    $this->configuration['documentColors'] = (int)$form_state->getValue('documentColors');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['font_backgroundcolors' => "", 'columns' => 5, 'documentColors' => 0 ];
  }

  /**
   * Builds the "font_names" configuration part of the CKEditor JS settings.
   *
   * @see getConfig()
   *
   * @param string $fonts
   *   The "font_names" setting.
   * @return array|FALSE
   *   An array containing the "fontSize_sizes" configuration, or FALSE when the
   *   syntax is invalid.
   */
  protected function generateFontColorSetting($fonts) {
    $font_colors = array();

    // Early-return when empty.
    $fonts = trim($fonts);
    if (empty($fonts)) {
      return FALSE;
    }

    $fonts = str_replace(array("\r\n", "\r"), "\n", $fonts);
    foreach (explode("\n", $fonts) as $font) {
      $font = trim($font);

      // Ignore empty lines in between non-empty lines.
      if (empty($font)) {
        continue;
      }

      // Match for patterns:
      // color value|Label
      // Thanks to https://regexr.com/39cgj for the source regex :) 
      $pattern = '@(?:\#|0x)(?:[a-f0-9]{3}|[a-f0-9]{6})\b|(?:rgb|hsl)a?\([^\)]*\)@i';

      if (!preg_match($pattern, $font)) {
        return FALSE;
      }

      $font_color = array();
      if(str_contains($font, '|')){
        list($color, $label) = explode('|', $font);
        $font_color['label'] = $label;
      }
      else {
        $color = $font;
      }

      $font_color['color'] = $color;
      $font_color['hasBorder'] = true;

      // Build subarray for CKEditor5 consumption.
      $font_colors[] = $font_color;
    }
    return $font_colors;
  }

}
