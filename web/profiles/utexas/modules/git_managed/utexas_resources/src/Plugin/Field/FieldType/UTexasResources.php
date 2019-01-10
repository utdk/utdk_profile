<?php

namespace Drupal\utexas_resources\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'utexas_resources' field type.
 *
 * @FieldType(
 *   id = "utexas_resources",
 *   label = @Translation("Resources"),
 *   description = @Translation("Headline, image, unlimited links, with optional copy text"),
 *   category = @Translation("UTexas"),
 *   default_widget = "utexas_resources",
 *   default_formatter = "utexas_resources"
 * )
 */
class UTexasResources extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['headline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Headline'))
      ->setRequired(FALSE);
    $properties['resource_items'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Resource Items'))
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
        'resource_items' => [
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
    $values['headline'] = ucfirst($random->word(mt_rand(5, 10)));
    for ($i = 0; $i < 3; $i++) {
      $values['resource_items'][$i]['item']['headline'] = ucfirst($random->word(mt_rand(5, 10)));
      $values['resource_items'][$i]['item']['copy']['value'] = $random->sentences(mt_rand(1, 2));
      $values['resource_items'][$i]['item']['copy']['format'] = 'restricted_html';
      // // Set of possible top-level domains for sample link value.
      $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
      // // Set random length for the domain name.
      $domain_length = mt_rand(7, 15);
      $values['resource_items'][$i]['item']['link']['url'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
      $values['resource_items'][$i]['item']['link']['title'] = ucfirst($random->word(mt_rand(5, 10)));
    }
    $values['resource_items'] = serialize($values['resource_items']);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $headline = $this->get('headline')->getValue();
    $resource_items = $this->get('resource_items')->getValue();
    return empty($headline) && empty($resource_items);
  }

}
