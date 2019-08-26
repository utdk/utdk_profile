<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_promo_unit' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_promo_unit_6",
 *   label = @Translation("Stacked Square (140x140, 1:1 ratio)"),
 *   field_types = {
 *     "utexas_promo_unit"
 *   },
 *   weight = 6,
 * )
 */
class UTexasPromoUnitSquareStackedFormatter extends UTexasPromoUnitSquareFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
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
