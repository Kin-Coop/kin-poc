<?php

declare(strict_types=1);

namespace Drupal\ckeditor_font\Plugin\CKEditor4To5Upgrade;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\ckeditor5\Plugin\CKEditor4To5UpgradePluginInterface;
use Drupal\ckeditor_font\Plugin\CKEditor5Plugin\Font;
use Drupal\Core\Plugin\PluginBase;
use Drupal\filter\FilterFormatInterface;

/**
 * Provides the CKEditor 4 to 5 upgrade for Drupal's Font CKEditor plugins.
 *
 * @CKEditor4To5Upgrade(
 *   id = "ckeditor_font",
 *   cke4_buttons = {
 *     "Font",
 *     "FontSize",
 *   },
 *   cke4_plugin_settings = {
 *     "font",
 *   },
 *   cke5_plugin_elements_subset_configuration = {
 *   }
 * )
 *
 * @internal
 *   Plugin classes are internal.
 */
class FontSizeAndFamily extends PluginBase implements CKEditor4To5UpgradePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function mapCKEditor4ToolbarButtonToCKEditor5ToolbarItem(string $cke4_button, HTMLRestrictions $text_format_html_restrictions): ?array {
    switch ($cke4_button) {
      case 'FontSize':
        return ['fontSize'];

      case 'Font':
        return ['fontFamily'];

      default:
        throw new \OutOfBoundsException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function mapCKEditor4SettingsToCKEditor5Configuration(string $cke4_plugin_id, array $cke4_plugin_settings): ?array {
    switch ($cke4_plugin_id) {
      case 'font':
        if (!isset($cke4_plugin_settings['font_sizes'])) {
          $font_sizes = [];
        }
        else {
          $font_sizes = Font::generateFontSetting($cke4_plugin_settings['font_sizes'], 'size');
        }

        if (!isset($cke4_plugin_settings['font_names'])) {
          $font_names = [];
        }
        else {
          $font_names_fixed = 
          $font_names = Font::generateFontSetting($cke4_plugin_settings['font_names'], 'family');
        }
        return [
          'ckeditor_font_font' => [
            'font_sizes' => $font_sizes,
            'font_names' => $font_names
          ],
        ];
      default:
        throw new \OutOfBoundsException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function computeCKEditor5PluginSubsetConfiguration(string $cke5_plugin_id, FilterFormatInterface $text_format): ?array {
      throw new \OutOfBoundsException();
  }

}
