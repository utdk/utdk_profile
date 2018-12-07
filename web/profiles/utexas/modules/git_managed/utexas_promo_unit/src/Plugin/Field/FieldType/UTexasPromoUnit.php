<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'utexas_promo_unit' field type.
 *
 * @FieldType(
 *   id = "utexas_promo_unit",
 *   label = @Translation("Promo Unit"),
 *   description = @Translation("A compound field with image, headline, copy text, and link."),
 *   category = "UTexas",
 *   default_widget = "utexas_promo_unit",
 *   default_formatter = "utexas_promo_unit"
 * )
 */
class UTexasPromoUnit extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['headline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Headline'))
      ->setRequired(FALSE);
    $properties['promo_unit_items'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Promo Unit Items'))
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
        'promo_unit_items' => [
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
      $values['promo_unit_items'][$i]['item']['headline'] = ucfirst($random->word(mt_rand(5, 10)));
      $values['promo_unit_items'][$i]['item']['copy']['value'] = $random->sentences(mt_rand(1, 2));
      $values['promo_unit_items'][$i]['item']['copy']['format'] = 'flex_html';
      // Attributes for sample image.
      static $images = [];
      $min_resolution = '100x100';
      $max_resolution = '600x600';
      $extensions = ['png', 'gif', 'jpg', 'jpeg'];
      $extension = array_rand(array_combine($extensions, $extensions));
      // Generate a max of 5 different images.
      if (!isset($images[$extension][$min_resolution][$max_resolution]) || count($images[$extension][$min_resolution][$max_resolution]) <= 5) {
        $tmp_file = drupal_tempnam('temporary://', 'generateImage_');
        $destination = $tmp_file . '.' . $extension;
        file_unmanaged_move($tmp_file, $destination);
        if ($path = $random->image(\Drupal::service('file_system')->realpath($destination), $min_resolution, $max_resolution)) {
          $image = File::create();
          $image->setFileUri($path);
          $image->setOwnerId(\Drupal::currentUser()->id());
          $image->setMimeType(\Drupal::service('file.mime_type.guesser')->guess($path));
          $image->setFileName(drupal_basename($path));
          $destination_dir = 'public://2018-11';
          file_prepare_directory($destination_dir, FILE_CREATE_DIRECTORY);
          $destination = $destination_dir . '/' . basename($path);
          $file = file_move($image, $destination);
          $images[$extension][$min_resolution][$max_resolution][$file->id()] = $file;
        }
        else {
          return [];
        }
      }
      else {
        // Select one of the images we've already generated for this field.
        $image_index = array_rand($images[$extension][$min_resolution][$max_resolution]);
        $file = $images[$extension][$min_resolution][$max_resolution][$image_index];
      }
      list($width, $height) = getimagesize($file->getFileUri());
      $values['promo_unit_items'][$i]['item']['image'][] = $file->id();
      // // Set of possible top-level domains for sample link value.
      $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
      // // Set random length for the domain name.
      $domain_length = mt_rand(7, 15);
      $values['promo_unit_items'][$i]['item']['link']['url'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
      $values['promo_unit_items'][$i]['item']['link']['title'] = ucfirst($random->word(mt_rand(5, 10)));
    }
    $values['promo_unit_items'] = serialize($values['promo_unit_items']);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $headline = $this->get('headline')->getValue();
    $promo_unit_items = $this->get('promo_unit_items')->getValue();
    return empty($headline) && empty($promo_unit_items);
  }

}
