<?php

namespace Drupal\opencollective_fields\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItemBase;

/**
 * Plugin implementation of the 'opencollective_collective_slug' field type.
 *
 * @FieldType(
 *   id = "opencollective_slug",
 *   label = @Translation("Open Collective Slug"),
 *   description = @Translation("A field containing the slug of an Open Collective colelctive."),
 *   default_widget = "string_textfield",
 *   default_formatter = "string"
 * )
 */
class Slug extends StringItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'max_length' => 255,
      'is_ascii' => FALSE,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => $field_definition->getSetting('is_ascii') === TRUE ? 'varchar_ascii' : 'varchar',
          'length' => 255,
          'binary' => $field_definition->getSetting('case_sensitive'),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();
    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints[] = $constraint_manager->create('ComplexData', [
      'value' => [
        'Length' => [
          'max' => 255,
          'maxMessage' => $this->t('%name: may not be longer than @max characters.', [
            '%name' => $this->getFieldDefinition()->getLabel(),
            '@max' => 255,
          ]),
        ],
        'Regex' => [
          'pattern' => '/^[a-z0-9-]+$/',
          'message' => $this->t('%name: may only contain lowercase letters, numbers, and dashes.', [
            '%name' => $this->getFieldDefinition()->getLabel(),
          ]),
        ],
      ],
    ]);
    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['value'] = $random->word(mt_rand(1, 32));
    return $values;
  }

}
