<?php

declare(strict_types = 1);

namespace Drupal\Tests\ckeditor_font\Kernel;

use Drupal\Tests\ckeditor5\Kernel\CKEditor4to5UpgradeCompletenessTest;

/**
 * @covers \Drupal\ckeditor_font\Plugin\CKEditor4To5Upgrade\Font
 * @group ckeditor_font
 * @group ckeditor5
 * @internal
 * @requires module ckeditor5
 */
class UpgradePathCompletenessTest extends CKEditor4to5UpgradeCompletenessTest {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['ckeditor_font'];

}
