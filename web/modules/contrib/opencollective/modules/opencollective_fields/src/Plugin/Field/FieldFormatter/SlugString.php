<?php

namespace Drupal\opencollective_fields\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;

/**
 * Plugin implementation of the 'opencollective_string' formatter.
 *
 * @FieldFormatter(
 *   id = "opencollective_string",
 *   label = @Translation("Plain text"),
 *   field_types = {
 *     "opencollective_slug"
 *   }
 * )
 */
class SlugString extends StringFormatter {}
