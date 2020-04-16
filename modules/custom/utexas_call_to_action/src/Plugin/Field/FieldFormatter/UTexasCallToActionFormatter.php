<?php

namespace Drupal\utexas_call_to_action\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

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
class UTexasCallToActionFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $url = $item->getUrl() ?: Url::fromRoute('<none>');
      $element[$delta] = [
        '#type' => 'link',
        '#title' => $item->title,
        '#options' => [],
      ];
      $element[$delta]['#url'] = $url;

      $icon_object = $item->getValue();
      if (isset($icon_object['options']['attributes']['class'])) {
        // Cast into array to comply with Drupal link options syntax.
        $icon_object['options']['attributes']['class'] = [$icon_object['options']['attributes']['class']];
      }
      $element[$delta]['#options']['attributes'] = isset($icon_object['options']['attributes']) ? $icon_object['options']['attributes'] : ['class' => []];
      $element[$delta]['#options']['attributes']['class'] += ['button', 'ut-btn'];
    }
    return $element;
  }

}
