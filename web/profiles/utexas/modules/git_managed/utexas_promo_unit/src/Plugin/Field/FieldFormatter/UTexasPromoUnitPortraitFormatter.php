<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'utexas_promo_unit' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_promo_unit_2",
 *   label = @Translation("Portrait (150x188, 4:5 ratio)"),
 *   field_types = {
 *     "utexas_promo_unit"
 *   },
 *   weight = 1,
 * )
 */
class UTexasPromoUnitPortraitFormatter extends UTexasPromoUnitDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $instances = [];
    foreach ($elements as $delta => $item) {
      $promo_unit_items = unserialize($items[$delta]->promo_unit_items);
      if (!empty($item['#promo_unit_items'])) {
        foreach ($item['#promo_unit_items'] as $key => &$instance) {
          $image = $promo_unit_items[$key]['item']['image'];
          $link = $promo_unit_items[$key]['item']['link']['url'];
          if (!empty($image)) {
            $responsive_image_style_name = 'utexas_responsive_image_pu_portrait';
            $item['#promo_unit_items'][$key]['image'] = $this->generateImageRenderArray($image[0], $responsive_image_style_name, $link);
          }
        }
      }
      $elements[$delta]['#promo_unit_items'] = $item['#promo_unit_items'];
      $elements[$delta]['#image_display'] = 'portrait-image';
    }
    return $elements;
  }

}
