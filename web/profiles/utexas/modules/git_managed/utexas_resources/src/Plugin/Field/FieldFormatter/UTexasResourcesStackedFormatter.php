<?php

namespace Drupal\utexas_resources\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_promo_unit' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_resources_2",
 *   label = @Translation("Stacked display"),
 *   field_types = {
 *     "utexas_resources"
 *   }
 * )
 */
class UTexasResourcesStackedFormatter extends UTexasResourcesDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements['#items'][$delta] = new \stdClass();
      $elements['#items'][$delta]->_attributes = [
        'class' => ['stacked-display'],
      ];
    }
    return $elements;
  }

}
