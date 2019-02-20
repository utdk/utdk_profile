<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_hero_3_left",
 *   label = @Translation("Style 3: White bottom pane with heading, subheading and burnt orange call to action, image anchored left"),
 *   field_types = {
 *     "utexas_hero"
 *   }
 * )
 */
class UTexasHeroStyle3FormatterLeft extends UTexasHeroStyle3Formatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements[$delta]['#anchor_position'] = 'left';
    }
    return $elements;
  }

}
