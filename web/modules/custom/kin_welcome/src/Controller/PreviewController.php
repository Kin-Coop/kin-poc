<?php

namespace Drupal\kin_welcome\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Renders a preview of the welcome modal slides.
 */
class PreviewController extends ControllerBase {

  /**
   * Builds the preview page and attaches the modal in preview mode.
   */
  public function preview() {
    // Load the same slide config the real modal uses.
    $config = $this->config('kin_welcome.slides');
    $slides = $config->get('slides') ?? [];

    // Build slides data for JavaScript, mirroring kin_welcome_page_attachments().
    // No contact ID is needed in preview: the final link is not followed.
    $slides_data = [];
    foreach ($slides as $slide) {
      $slides_data[] = [
        'title' => $slide['title'] ?? '',
        'body' => $slide['body'] ?? '',
        'link_text' => $slide['link_text'] ?? NULL,
        'link_url' => $slide['link_url'] ?? NULL,
        'currentContactId' => NULL,
      ];
    }

    $build = [];
    $build['#markup'] = '<p>' . $this->t('This is a preview of the welcome modal. Closing it will not affect any user account, and nothing is saved.') . '</p>';
    $build['#attached']['library'][] = 'kin_welcome/welcome_modal';
    $build['#attached']['drupalSettings']['kinWelcome'] = [
      'slides' => $slides_data,
      // In preview mode the modal never calls the mark-shown endpoint.
      'preview' => TRUE,
    ];

    // Prevent caching so edits to slides always show on reload.
    $build['#cache']['max-age'] = 0;

    return $build;
  }

}
