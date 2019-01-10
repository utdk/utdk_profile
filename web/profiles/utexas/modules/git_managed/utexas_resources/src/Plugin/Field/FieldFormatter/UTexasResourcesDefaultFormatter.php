<?php

namespace Drupal\utexas_resources\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_promo_unit' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_resources",
 *   label = @Translation("Default 'Resources' display"),
 *   field_types = {
 *     "utexas_resources"
 *   }
 * )
 */
class UTexasResourcesDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $instances = [];
      $resource_items = unserialize($item->resource_items);
      if (!empty($resource_items)) {
        foreach ($resource_items as $key => $instance) {
          $i = $instance['item'];
          if (!empty($i['headline'])) {
            $instances[$key]['headline'] = $i['headline'];
          }
          if (!empty($i['copy']['value'])) {
            $instances[$key]['copy'] = check_markup($i['copy']['value'], $i['copy']['format']);
          }
          if (!empty($i['copy']['value'])) {
            $instances[$key]['copy'] = check_markup($i['copy']['value'], $i['copy']['format']);
          }
          if (!empty($i['links'])) {
            foreach ($i['links'] as $l) {
              // Ensure that links without title text print the URL.
              $link_url = Url::fromUri($l['url']);
              if (empty($l['title'])) {
                $url = $link_url;
                $url->setAbsolute();
                $link_title = $url->toString();
              }
              else {
                $link_title = $l['title'];
              }
              $link_options = [
                'attributes' => [
                  'class' => [
                    'ut-link--darker',
                  ],
                ],
              ];
              $link_url->setOptions($link_options);
              $link = Link::fromTextAndUrl($link_title, $link_url);
              $instances[$key]['links'][] = $link;
            }
          }
          if (!empty($i['image'])) {
            $responsive_image_style_name = 'utexas_responsive_image_resource';
            $instances[$key]['image'] = $this->generateImageRenderArray($i['image'][0], $responsive_image_style_name);
          }
        }
      }
      $elements[] = [
        '#theme' => 'utexas_resources',
        '#headline' => $item->headline,
        '#resource_items' => $instances,
      ];
    }
    return $elements;

  }

  /**
   * Helper method to prepare image array.
   */
  protected function generateImageRenderArray($image, $responsive_image_style_name) {
    // Initialize image render array as false in case that images are not found.
    $image_render_array = FALSE;
    if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($image)) {
      $media_attributes = $media->get('field_utexas_media_image')->getValue();
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
    return $image_render_array;
  }

}
