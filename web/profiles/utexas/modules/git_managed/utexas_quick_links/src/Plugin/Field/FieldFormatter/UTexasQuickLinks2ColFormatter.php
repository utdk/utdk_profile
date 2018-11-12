<?php

namespace Drupal\utexas_quick_links\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_quick_links' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_quick_links_2",
 *   label = @Translation("Display Links in 2 columns."),
 *   field_types = {
 *     "utexas_quick_links"
 *   },
 *    weight = 2,
 * )
 */
class UTexasQuickLinks2ColFormatter extends UTexasQuickLinksDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements[$delta]['#columns'] = 'two';
    }
    return $elements;
  }

}
