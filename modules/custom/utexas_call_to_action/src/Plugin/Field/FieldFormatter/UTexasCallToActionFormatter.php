<?php

namespace Drupal\utexas_call_to_action\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;

/**
 * Plugin implementation of the 'utexas_call_to_action_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_call_to_action_formatter",
 *   label = @Translation("UTexas Call to Action"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class UTexasCallToActionFormatter extends LinkFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Load items and append cta button classes before link conversion.
    foreach ($items as $delta => $item) {
      $cta_values = $item->getValue();
      $link_classes = [
        'button',
        'ut-btn',
      ];
      if (isset($cta_values['options']['attributes']['class'])) {
        // Append any existing class to the classes that should be rendered.
        $link_classes[] = $cta_values['options']['attributes']['class'];
      }
      $cta_values['options']['attributes']['class'] = $link_classes;
      $items[$delta]->setValue($cta_values);
    }
    // Call Link viewElements method to convert CTA into link with our classes.
    $element = parent::viewElements($items, $langcode);
    return $element;
  }

}
