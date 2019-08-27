<?php

namespace Drupal\utexas_photo_content_area\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_photo_content_area' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_photo_content_area_2",
 *   label = @Translation("Stacked display"),
 *   field_types = {
 *     "utexas_photo_content_area"
 *   },
 *    weight = 2,
 * )
 */
class UTexasPhotoContentAreaStackedFormatter extends UTexasPhotoContentAreaDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $elements['#attributes']['class'][] = 'stacked-display';
    return $elements;
  }

}
