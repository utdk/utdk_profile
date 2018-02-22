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
    $element['social_account_name'] = [
      '#type' => 'select',
      '#title' => 'Website',
      '#options' => UTexasSocialLinkOptions::getOptionsArray(),
      '#default_value' => isset($items[$delta]->social_account_name) ? $items[$delta]->social_account_name : NULL,
    ];
    $element['social_account_url'] = [
      '#type' => 'url',
      '#title' => 'URL',
      '#default_value' => isset($items[$delta]->social_account_url) ? $items[$delta]->social_account_url : NULL,
      '#placeholder' => 'https://media-site-name.com/our-handle',
    ];
    $element['#attached']['library'][] = 'utexas_block_social_links/form';
    return $element;
  }

}
