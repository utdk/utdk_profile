<?php

namespace Drupal\utexas_featured_highlight\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * Plugin implementation of the 'utexas_featured_highlight' field type.
 *
 * @FieldType(
 *   id = "utexas_featured_highlight",
 *   label = @Translation("Featured Highlight"),
 *   description = @Translation("A field with headline, media, date, copy, & link."),
 *   category = "UTexas",
 *   default_widget = "utexas_featured_highlight",
 *   default_formatter = "utexas_featured_highlight"
 * )
 */
class UTexasFeaturedHighlight extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['media'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Media'))
      ->setRequired(FALSE);
    $properties['headline'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Headline'))
      ->setRequired(FALSE);
    $properties['date'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Date'))
      ->setRequired(FALSE);
    $properties['copy_value'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Copy value'))
      ->setRequired(FALSE);
    $properties['copy_format'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Copy format'))
      ->setRequired(FALSE);
    $properties['link_uri'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Link URI'))
      ->setRequired(FALSE);
    $properties['link_text'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Link Text'))
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
        'headline' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'date' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'copy_value' => [
          'type' => 'text',
          'size' => 'normal',
          'binary' => FALSE,
        ],
        'copy_format' => [
          'type' => 'varchar',
          'length' => 255,
          'binary' => FALSE,
        ],
        'link_uri' => [
          'type' => 'varchar',
          'length' => 512,
          'binary' => FALSE,
        ],
        'link_text' => [
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

    $values['headline'] = $random->word(10);
    $values['date'] = '2019-01-01';
    $values['copy_value'] = $random->sentences(8);
    $values['copy_format'] = 'restricted_html';

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

    // Set of possible top-level domains for sample link value.
    $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
    // Set random length for the domain name.
    $domain_length = mt_rand(7, 15);
    // We only need a URL here.
    $values['link_uri'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
    $values['link_text'] = $random->word($domain_length);
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $media = $this->get('media')->getValue();
    $headline = $this->get('headline')->getValue();
    $date = $this->get('date')->getValue();
    $copy = $this->get('copy_value')->getValue();
    $link = $this->get('link_uri')->getValue();
    return empty($media) && empty($headline) && empty($date) && empty($copy) && empty($link);
  }

}
