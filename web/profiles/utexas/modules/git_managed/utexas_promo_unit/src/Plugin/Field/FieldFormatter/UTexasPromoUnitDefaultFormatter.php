<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_promo_unit' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_promo_unit",
 *   label = @Translation("Landscape (220x140, 11:7 ratio)"),
 *   field_types = {
 *     "utexas_promo_unit"
 *   }
 * )
 */
class UTexasPromoUnitDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $cache_tags = $this->generateCacheTags('utexas_responsive_image_pu_landscape');
    foreach ($items as $delta => $item) {
      $instances = [];
      $promo_unit_items = unserialize($item->promo_unit_items);
      if (!empty($promo_unit_items)) {
        foreach ($promo_unit_items as $key => $instance) {
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
          if (!empty($i['link']['url'])) {
            // Ensure that links without title text print the URL.
            $link_url = Url::fromUri($i['link']['url']);
            if (empty($i['link']['title'])) {
              $url = Url::fromUri($i['link']['url']);
              $url->setAbsolute();
              $link_title = $url->toString();
            }
            else {
              $link_title = $i['link']['title'];
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
            $instances[$key]['link'] = $link;
          }
          if (!empty($i['image'])) {
            $image = is_array($i['image']) ? $i['image'][0] : $i['image'];
            $responsive_image_style_name = 'utexas_responsive_image_pu_landscape';
            $instances[$key]['image'] = $this->generateImageRenderArray($image, $responsive_image_style_name, $i['link']['url'], $cache_tags);
          }
        }
      }
      $elements[$delta] = [
        '#theme' => 'utexas_promo_unit',
        '#headline' => $item->headline,
        '#promo_unit_items' => $instances,
        '#image_display' => 'landscape-image',
      ];
      $elements[$delta]['#attached']['library'][] = 'utexas_promo_unit/promo-units';
    }
    return $elements;

  }

  /**
   * Helper method to generate cache tags.
   */
  protected function generateCacheTags($responsive_image_style_name) {
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
    return $cache_tags;
  }

  /**
   * Helper method to prepare image array.
   */
  protected function generateImageRenderArray($image, $responsive_image_style_name, $link_url, $cache_tags) {
    // Initialize image render array as false in case that images are not found.
    $image_render_array = FALSE;
    if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($image)) {
      $media_attributes = $media->get('field_utexas_media_image')->getValue();
      if (!empty($link_url)) {
        $link = Url::fromUri($link_url);
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
    }
    return $image_render_array;
  }

}
