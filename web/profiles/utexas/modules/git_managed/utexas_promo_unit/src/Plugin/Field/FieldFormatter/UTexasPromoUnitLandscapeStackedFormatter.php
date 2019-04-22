<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_promo_unit' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_promo_unit_4",
 *   label = @Translation("Stacked Landscape(220x140, 11:7 ratio)"),
 *   field_types = {
 *     "utexas_promo_unit"
 *   },
 *   weight = 4,
 * )
 */
class UTexasPromoUnitLandscapeStackedFormatter extends UTexasPromoUnitDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $instances = [];
    foreach ($elements as $delta => $item) {
      foreach ($items as $delta => $item) {
        $elements['#items'][$delta] = new \stdClass();
        $elements['#items'][$delta]->_attributes = [
          'class' => ['stacked-display'],
        ];
      }
    }
    return $elements;
  }

}
