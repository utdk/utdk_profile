<?php

namespace Drupal\utexas_quick_links\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_quick_links' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_quick_links_3",
 *   label = @Translation("Display Links in 3 columns."),
 *   field_types = {
 *     "utexas_quick_links"
 *   },
 *   weight = 3,
 * )
 */
class UTexasQuickLinks3ColFormatter extends UTexasQuickLinksDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements[$delta]['#columns'] = 'three';
    }
    return $elements;
  }

}
