<?php

namespace Drupal\utexas_flex_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_flex_list_accordion' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_flex_list_accordion",
 *   label = @Translation("Accordion"),
 *   field_types = {
 *     "utexas_flex_list"
 *   }
 * )
 */
class UTexasFlexListAccordionFormatter extends UTexasFlexListFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $elements['#attached']['library'][] = 'utexas_flex_list/accordion';
    $elements['#theme_info'] = ['formatter_name' => 'accordion'];
    return $elements;
  }

}
