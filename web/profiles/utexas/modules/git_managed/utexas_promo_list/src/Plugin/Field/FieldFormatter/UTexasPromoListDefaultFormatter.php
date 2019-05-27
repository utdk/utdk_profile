<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'utexas_promo_list' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_promo_list",
 *   label = @Translation("Single list full (1 item per row)"),
 *   field_types = {
 *     "utexas_promo_list"
 *   }
 * )
 */
class UTexasPromoListDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $responsive_image_style_name = 'utexas_responsive_image_pl';
    // Collect cache tags.
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
      $instances = [];
      $promo_list_items = is_string($item->promo_list_items) ? unserialize($item->promo_list_items) : $item->promo_list_items;
      if (!empty($promo_list_items)) {
        foreach ($promo_list_items as $key => $instance) {
          $i = $instance['item'];
          if (!empty($i['headline'])) {
            $instances[$key]['headline'] = $i['headline'];
          }
          if (!empty($i['copy']['value'])) {
            $instances[$key]['copy'] = check_markup($i['copy']['value'], $i['copy']['format']);
          }
          if (!empty($i['link'])) {
            $url = Url::fromUri($i['link']);
            $url->setAbsolute();
            $instances[$key]['link'] = $url->toString();
          }
          $image_render_array = [];
          $image = is_array($i['image']) ? $i['image'][0] : $i['image'];
          if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($image)) {
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
                '#item_attributes' => ['class' => ['ut-img--fluid']],
                '#responsive_image_style_id' => $responsive_image_style_name,
                '#url' => $instances[$key]['link'] ?? '',
                '#cache' => [
                  'tags' => $cache_tags,
                ],
              ];
            }
            // Add the file entity to the cache dependencies.
            // This will clear our cache when this entity updates.
            $renderer = \Drupal::service('renderer');
            $renderer->addCacheableDependency($image_render_array, $file);
            $instances[$key]['image'] = $image_render_array;
          }
        }
      }
      $elements[] = [
        '#theme' => 'utexas_promo_list',
        '#headline' => $item->headline,
        '#promo_list_items' => $instances,
        '#wrapper' => '',
      ];
    }
    $elements['#attached']['library'][] = 'utexas_promo_list/promo-lists';
    return $elements;
  }

}
