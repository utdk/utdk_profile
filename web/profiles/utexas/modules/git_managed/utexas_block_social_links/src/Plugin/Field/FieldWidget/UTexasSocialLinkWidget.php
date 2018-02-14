<?php

namespace Drupal\utexas_block_social_links\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\utexas_block_social_links\Services\UTexasSocialLinkOptions;

/**
 * Plugin implementation of the 'utexas_social_link_widget' widget.
 *
 * @FieldWidget(
 *   id = "utexas_social_link_widget",
 *   label = @Translation("UTexas Social Link"),
 *   field_types = {
 *     "utexas_social_link_field"
 *   }
 * )
 */
class UTexasSocialLinkWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['icon'] = [
      '#type' => 'select',
      '#title' => 'Website',
      '#options' => UTexasSocialLinkOptions::getOptionsArray(),
      '#default_value' => isset($items[$delta]->icon) ? $items[$delta]->icon : NULL,
    ];
    $element['url'] = [
      '#type' => 'url',
      '#title' => 'URL',
      '#default_value' => isset($items[$delta]->url) ? $items[$delta]->url : NULL,
      '#placeholder' => 'https://name-of-media-site.com/our-name',
    ];
    $element['#attached']['library'][] = 'utexas_block_social_links/form';
    return $element;
  }

}
