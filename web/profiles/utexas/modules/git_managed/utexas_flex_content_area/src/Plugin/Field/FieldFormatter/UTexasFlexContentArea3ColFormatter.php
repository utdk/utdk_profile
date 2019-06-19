<?php

namespace Drupal\utexas_flex_content_area\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_flex_content_area' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_flex_content_area_3",
 *   label = @Translation("Display 3 items per row"),
 *   field_types = {
 *     "utexas_flex_content_area"
 *   }
 * )
 */
class UTexasFlexContentArea3ColFormatter extends UTexasFlexContentAreaDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements['#items'][$delta] = new \stdClass();
      $elements['#items'][$delta]->_attributes = [
        'class' => ['ut-flex-content-area', 'three-col'],
      ];
    }
    return $elements;
  }

}
