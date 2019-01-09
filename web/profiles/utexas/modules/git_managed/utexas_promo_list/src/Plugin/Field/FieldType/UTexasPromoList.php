<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'utexas_promo_list' field type.
 *
 * @FieldType(
 *   id = "utexas_promo_list",
 *   label = @Translation("Promo List"),
 *   description = @Translation("A field with headline, image, copy, & link."),
 *   category = @Translation("UTexas"),
 *   default_widget = "utexas_promo_list",
 *   default_formatter = "utexas_promo_list"
 * )
 */
class UTexasPromoList extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['headline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Headline'))
      ->setRequired(FALSE);
    $properties['promo_list_items'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Promo List Items'))
      ->setRequired(FALSE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'headline' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'promo_list_items' => [
          'type' => 'blob',
          'size' => 'normal',
        ],
      ],
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['headline'] = $random->word(mt_rand(1, 3));
    $values['promo_list_items'] = [];
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $headline = $this->get('headline')->getValue();
    $promo_list_items = $this->get('promo_list_items')->getValue();
    return empty($headline) && empty($promo_list_items);
  }

}
