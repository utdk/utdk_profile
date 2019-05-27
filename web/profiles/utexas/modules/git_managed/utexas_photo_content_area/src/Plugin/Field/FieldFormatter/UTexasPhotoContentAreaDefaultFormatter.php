<?php

namespace Drupal\utexas_photo_content_area\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'utexas_photo_content_area' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_photo_content_area",
 *   label = @Translation("Default display"),
 *   field_types = {
 *     "utexas_photo_content_area"
 *   }
 * )
 */
class UTexasPhotoContentAreaDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // Collect cache tags to be added for each item in the field.
    $responsive_image_style_name = 'utexas_responsive_image_pca';
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
      $links = unserialize($item->links);
      // Ensure that links without title text print the URL.
      if (!empty($links)) {
        foreach ($links as &$link) {
          if (empty($link['title'])) {
            $url = Url::fromUri($link['url']);
            $url->setAbsolute();
            $link['title'] = $url->toString();
          }
        }
      }
      else {
        $links = [];
      }
      $image_render_array = [];
      if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($item->image)) {
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
        $image_render_array = [];
        if ($file = \Drupal::entityTypeManager()->getStorage('file')->load($media_attributes[0]['target_id'])) {
          $image = new \stdClass();
          $image->title = NULL;
          $image->alt = $media_attributes[0]['alt'];
          $image->entity = $file;
          $image->width = NULL;
          $image->height = NULL;
          $image_render_array = [
            '#theme' => 'responsive_image_formatter',
            '#item' => $image,
            '#item_attributes' => [],
            '#responsive_image_style_id' => $responsive_image_style_name,
            '#cache' => [
              'tags' => $cache_tags,
            ],
          ];
        }
        // Add the file entity to the cache dependencies.
        // This will clear our cache when this entity updates.
        $renderer = \Drupal::service('renderer');
        $renderer->addCacheableDependency($image_render_array, $file);
      }
      $elements[] = [
        '#theme' => 'utexas_photo_content_area',
        '#image' => $image_render_array,
        '#photo_credit' => $item->photo_credit,
        '#headline' => $item->headline,
        '#copy' => check_markup($item->copy_value, $item->copy_format),
        '#links' => $links,
      ];
    }
    $elements['#attached']['library'][] = 'utexas_photo_content_area/photo-content-area';
    return $elements;
  }

}
