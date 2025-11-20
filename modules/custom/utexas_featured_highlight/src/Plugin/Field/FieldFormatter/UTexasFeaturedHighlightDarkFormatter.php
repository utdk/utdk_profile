<?php

namespace Drupal\utexas_featured_highlight\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_featured_highlight' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_featured_highlight_3",
 *   label = @Translation("Charcoal (Dark)"),
 *   field_types = {
 *     "utexas_featured_highlight"
 *   }
 * )
 */
class UTexasFeaturedHighlightDarkFormatter extends UTexasFeaturedHighlightDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements[$delta]['#style'] = 'dark';
    }
    return $elements;
  }

}
