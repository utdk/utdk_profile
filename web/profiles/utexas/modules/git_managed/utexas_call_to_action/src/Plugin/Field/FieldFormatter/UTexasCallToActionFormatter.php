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
    $entity = $items->getEntity();

    foreach ($items as $delta => $item) {
      $url = $item->getUrl() ?: Url::fromRoute('<none>');
      $element[$delta] = [
        '#type' => 'link',
        '#title' => $item->title,
        '#options' => [],
      ];
      $element[$delta]['#url'] = $url;
      $element[$delta]['#options'] += ['attributes' => ['class' => ['ut-btn', 'button']]];
    }
    return $element;
  }

}
