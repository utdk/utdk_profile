<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_promo_list' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_promo_list_3",
 *   label = @Translation("Two lists, side-by-side"),
 *   field_types = {
 *     "utexas_promo_list"
 *   }
 * )
 */
class UTexasPromoListTwoColumnFormatter extends UTexasPromoListDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => $item) {
      $elements[$delta]['#wrapper'] = 'row';
      $elements[$delta]['#columns'] = 'col-12 col-lg-6';
    }
    return $elements;
  }

}
