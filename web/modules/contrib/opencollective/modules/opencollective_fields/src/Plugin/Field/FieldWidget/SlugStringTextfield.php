<?php

namespace Drupal\opencollective_fields\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;

/**
 * Plugin implementation of the 'opencollective_string' widget.
 *
 * @FieldWidget(
 *   id = "opencollective_string",
 *   label = @Translation("Textfield"),
 *   field_types = {
 *     "opencollective_slug"
 *   }
 * )
 */
class SlugStringTextfield extends StringTextfieldWidget {}
