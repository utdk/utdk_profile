<?php

namespace Drupal\utexas_flex_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Utility\Html;

/**
 * Plugin implementation of the 'bootstrap_horizontal_tabs' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_flex_list_horizontal_tabs",
 *   label = @Translation("Horizontal Tabs"),
 *   field_types = {
 *     "utexas_flex_list"
 *   }
 * )
 */
class UTexasFlexListHorizontalTabsFormatter extends UTexasFlexListFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $elements['#attached']['library'][] = 'utexas_flex_list/horizontal-tabs';
    $elements['#theme_info'] = ['formatter_name' => 'htabs'];
    $elements['#instance_id'] = Html::getUniqueId('horizontal-tab');
    return $elements;
  }

}
