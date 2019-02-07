<?php
namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
/**
 * Plugin implementation of the 'utexas_hero' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_hero_5",
 *   label = @Translation("Style 5: Medium image, floated right, with large heading, subheading and burnt orange call-to-action"),
 *   field_types = {
 *     "utexas_hero"
 *   }
 * )
 */
class UTexasHeroStyle5Formatter extends UTexasHeroDefaultFormatter {
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => $item) {
      // @todo: make changes to the item elements as necessary for this formatter.
    }
    return $elements;
  }
}