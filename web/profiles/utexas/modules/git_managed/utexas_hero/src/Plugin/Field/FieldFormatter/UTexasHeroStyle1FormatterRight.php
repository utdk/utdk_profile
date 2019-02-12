<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_hero_1_right",
<<<<<<< HEAD
 *   label = @Translation("Style 1: Bold heading & subheading on burnt orange background, image anchored right"),
=======
 *   label = @Translation("Style 1: Bold heading & subheading on burnt orange background, floated right"),
>>>>>>> a801037... Adding left and right hero style 1 formatters with view modes and theme changes
 *   field_types = {
 *     "utexas_hero"
 *   }
 * )
 */
class UTexasHeroStyle1FormatterRight extends UTexasHeroStyle1Formatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($items as $delta => $item) {
      $elements[$delta]['#anchor_position'] = 'right';
    }
    return $elements;
  }

}
