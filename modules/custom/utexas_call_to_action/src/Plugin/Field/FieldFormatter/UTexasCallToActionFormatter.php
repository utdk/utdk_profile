<?php

namespace Drupal\utexas_call_to_action\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\Markup;
use Drupal\link\Plugin\Field\FieldFormatter\LinkFormatter;
use Drupal\utexas_form_elements\RenderElementHelper;

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
      $cta_values['title'] = RenderElementHelper::filterSingleLineText($cta_values['title']);
      $items[$delta]->setValue($cta_values);
    }
    // Call Link viewElements method to convert CTA into link with our classes.
    $elements = parent::viewElements($items, $langcode);
    // Allow select markup to be rendered.
    foreach ($elements as &$element) {
      $element['#title'] = Markup::create($element['#title']);
    }
    return $elements;
  }

}
