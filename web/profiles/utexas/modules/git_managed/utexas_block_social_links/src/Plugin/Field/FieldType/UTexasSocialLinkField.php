<?php

namespace Drupal\utexas_block_social_links\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\utexas_block_social_links\Services\UTexasSocialLinkOptions;

/**
 * Plugin implementation of the 'utexas_social_link_field' field type.
 *
 * @FieldType(
 *   id = "utexas_social_link_field",
 *   label = @Translation("UTexas Social Link"),
 *   description = @Translation("Defines a tuple field with social icon selector & URL entry"),
 *   default_widget = "utexas_social_link_widget",
 *   default_formatter = "utexas_social_link_formatter"
 * )
 */
class UTexasSocialLinkField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using TranslatableMarkup class directly.
    $properties['icon'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Icon key'))
      ->setSetting('case_sensitive', TRUE)
      ->setRequired(TRUE);
    $properties['url'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('URL'))
      ->setSetting('case_sensitive', TRUE)
      ->setRequired(TRUE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'icon' => [
          'type' => 'varchar_ascii',
          'length' => 255,
          'binary' => TRUE,
        ],
        'url' => [
          'type' => 'varchar_ascii',
          'length' => 512,
          'binary' => TRUE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
    $constraints[] = $constraint_manager->create('ComplexData', [
      'url' => [
        'Length' => [
          'max' => 512,
          'maxMessage' => t('%name: may not be longer than @max characters.', [
            '%name' => $this->getFieldDefinition()->getLabel(),
            '@max' => 512,
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
    $options = UTexasSocialLinkOptions::getOptionsArray();
    $values['icon'] = array_rand($options);
    $values['url'] = 'http://' . Random::word(10) . '.com';
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('url')->getValue();
    return $value === NULL || $value === '';
  }

}
