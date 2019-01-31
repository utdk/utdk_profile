<?php

namespace Drupal\utexas_image_link\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'utexas_image_link' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_image_link",
 *   label = @Translation("UTexas Image Link Formatter"),
 *   field_types = {
 *     "utexas_image_link"
 *   }
 * )
 */
class UTexasImageLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $responsive_image_style_name = 'utexas_responsive_image_il';
    // Collect cache tags to be added for each item in the field.
    $responsive_image_style = \Drupal::entityTypeManager()->getStorage('responsive_image_style')->load($responsive_image_style_name);
    $image_styles_to_load = [];
    $cache_tags = [];
    if ($responsive_image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $responsive_image_style->getCacheTags());
      $image_styles_to_load = $responsive_image_style->getImageStyleIds();
    }
    $image_styles = \Drupal::entityTypeManager()->getStorage('image_style')->loadMultiple($image_styles_to_load);
    foreach ($image_styles as $image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $image_style->getCacheTags());
    }
    foreach ($items as $delta => $item) {
      if (!empty($item->link)) {
        $url = Url::fromUri($item->link);
        $link = $url->toString();
      }
      if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($item->image)) {
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
        $image_render_array = [];
        if ($file = \Drupal::entityTypeManager()->getStorage('file')->load($media_attributes[0]['target_id'])) {
          $image = new \stdClass();
          $image->title = NULL;
          $image->alt = $media_attributes[0]['alt'];
          $image->entity = $file;
          $image->uri = $file->getFileUri();
          $image->width = NULL;
          $image->height = NULL;
          $image_render_array = [
            '#theme' => 'responsive_image_formatter',
            '#item' => $image,
            '#item_attributes' => [],
            '#responsive_image_style_id' => $responsive_image_style_name,
            '#url' => $link ?? '',
            '#cache' => [
              'tags' => $cache_tags,
            ],
          ];
        }

        // Add the file entity to the cache dependencies.
        // This will clear our cache when this entity updates.
        $renderer = \Drupal::service('renderer');
        $renderer->addCacheableDependency($image_render_array, $file);
        $elements[] = [
          '#theme' => 'utexas_image_link',
          '#image' => $image_render_array,
          '#link' => $link ?? '',
        ];
      }
    }
    return $elements;
  }

}
