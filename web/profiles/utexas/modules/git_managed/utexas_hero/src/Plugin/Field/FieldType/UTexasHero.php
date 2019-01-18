<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\core\Language\Language;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * Plugin implementation of the 'utexas_hero' field type.
 *
 * @FieldType(
 *   id = "utexas_hero",
 *   label = @Translation("Hero"),
 *   description = @Translation("Large-display media field, with heading/subheading/link"),
 *   category = "UTexas",
 *   default_widget = "utexas_hero",
 *   default_formatter = "utexas_hero"
 * )
 */
class UTexasHero extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['media'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Image'))
      ->setRequired(FALSE);
    $properties['heading'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Heading'))
      ->setRequired(FALSE);
    $properties['subheading'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Sub-heading'))
      ->setRequired(FALSE);
    $properties['caption'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Caption'))
      ->setRequired(FALSE);
    $properties['credit'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('credit'))
      ->setRequired(FALSE);
    $properties['link_uri'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Link URI'))
      ->setRequired(FALSE);
    $properties['link_title'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Link Title'))
      ->setRequired(FALSE);
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'media' => [
          'type' => 'int',
        ],
        'heading' => [
          'type' => 'varchar',
          'length' => 512,
          'binary' => FALSE,
        ],
        'subheading' => [
          'type' => 'varchar',
          'length' => 512,
          'binary' => FALSE,
        ],
        'caption' => [
          'type' => 'varchar',
          'length' => 512,
          'binary' => FALSE,
        ],
        'credit' => [
          'type' => 'varchar',
          'length' => 512,
          'binary' => FALSE,
        ],
        'link_uri' => [
          'type' => 'varchar',
          'length' => 512,
          'binary' => FALSE,
        ],
        'link_title' => [
          'type' => 'varchar',
          'length' => 512,
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
    $values['heading'] = $random->word(mt_rand(1, 255));
    $values['subheading'] = $random->sentences(2);
    $values['caption'] = $random->sentences(2);
    $values['credit'] = $random->sentences(2);
    $values['link_title'] = $random->sentences(1);
    // // Set of possible top-level domains for sample link value.
    $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
    // // Set random length for the domain name.
    $domain_length = mt_rand(7, 15);
    $values['link_uri'] = 'https://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
    // Attributes for sample image.
    static $images = [];
    $min_resolution = '2280x1232';
    $max_resolution = '2280x1232';
    $extensions = ['png', 'gif', 'jpg', 'jpeg'];
    $extension = array_rand(array_combine($extensions, $extensions));
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
    $image_media = Media::create([
      'name' => 'Image 1',
      'bundle' => 'utexas_image',
      'uid' => '1',
      'langcode' => Language::LANGCODE_NOT_SPECIFIED,
      'status' => '1',
      'field_utexas_media_image' => [
        'target_id' => $file->id(),
        'alt' => t('Test Alt Text'),
        'title' => t('Test Title Text'),
      ],
    ]);
    $image_media->save();
    $values['media'] = $image_media->id();
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return $this->get('media')->getValue() == 0;
  }

}
