<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

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
    $random = new Random();
    $values['headline'] = ucfirst($random->word(mt_rand(5, 10)));
    for ($i = 0; $i < 3; $i++) {
      $values['promo_list_items'][$i]['item']['headline'] = ucfirst($random->word(mt_rand(5, 10)));
      $values['promo_list_items'][$i]['item']['copy']['value'] = $random->sentences(mt_rand(1, 2));
      $values['promo_list_items'][$i]['item']['copy']['format'] = 'flex_html';
      // Attributes for sample image.
      $min_resolution = '100x100';
      $max_resolution = '600x600';
      $extensions = ['png', 'gif', 'jpg', 'jpeg'];
      $extension = array_rand(array_combine($extensions, $extensions));
      /** @var \Drupal\Core\File\FileSystemInterface $file_system */
      $file_system = \Drupal::service('file_system');
      $tmp_file = $file_system->tempnam('temporary://', 'generateImage_');
      $destination = $tmp_file . '.' . $extension;
      try {
        $file_system->move($tmp_file, $destination);
      }
      catch (FileException $e) {
        // Ignore failed move.
      }
      if ($path = $random->image($file_system->realpath($destination), $min_resolution, $max_resolution)) {
        $image = File::create();
        $image->setFileUri($path);
        $image->setOwnerId(\Drupal::currentUser()->id());
        $image->setMimeType(\Drupal::service('file.mime_type.guesser')->guess($path));
        $image->setFileName($file_system->basename($path));
        $destination_dir = 'public://generated_sample';
        $file_system->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY);
        $destination = $destination_dir . '/' . basename($path);
        $file = file_move($image, $destination);
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
        $values['promo_list_items'][$i]['item']['image'][] = $image_media->id();
      }
      else {
        $values['promo_list_items'][$i]['item']['image'][] = 0;
      }
      // Set of possible top-level domains for sample link value.
      $tlds = ['com', 'net', 'gov', 'org', 'edu', 'biz', 'info'];
      // Set random length for the domain name.
      $domain_length = mt_rand(7, 15);
      $values['promo_list_items'][$i]['item']['link']['uri'] = 'http://www.' . $random->word($domain_length) . '.' . $tlds[mt_rand(0, (count($tlds) - 1))];
    }
    $values['promo_list_items'] = serialize($values['promo_list_items']);
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
