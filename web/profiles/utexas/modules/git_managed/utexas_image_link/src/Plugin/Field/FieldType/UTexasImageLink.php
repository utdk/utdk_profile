<?php

namespace Drupal\utexas_image_link\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'utexas_image_link' field type.
 *
 * @FieldType(
 *   id = "utexas_image_link",
 *   label = @Translation("Image Link"),
 *   description = @Translation("Linked image"),
 *   category = @Translation("UTexas"),
 *   default_widget = "utexas_image_link",
 *   default_formatter = "utexas_image_link"
 * )
 */
class UTexasImageLink extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties['image'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Image'))
      ->setRequired(FALSE);
    $properties['link'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Link'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'image' => [
          'type' => 'int',
        ],
        'link' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
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
    $values['image'] = $file->id();

    // Set of possible top-level domains for sample link value.
    $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
    // Set random length for the domain name.
    $domain_length = mt_rand(7, 15);
    // We only need a URL here.
    $values['link'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $image = $this->get('image')->getValue();
    $link = $this->get('link')->getValue();
    return empty($image) && empty($link);
  }

}
