<?php

namespace Drupal\utexas_image_link\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'utexas_image_link' widget.
 *
 * @FieldWidget(
 *   id = "utexas_image_link",
 *   label = @Translation("UTexas Image Link"),
 *   field_types = {
 *     "utexas_image_link"
 *   }
 * )
 */
class UTexasImageLinkWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['image'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => ['utexas_image'],
      '#delta' => $delta,
      '#cardinality' => 1,
      '#title' => $this->t('Image'),
      '#default_value' => isset($items[$delta]->image) ? $items[$delta]->image : 0,
      '#description' => $this->t('This image will fill the width of the region it is placed in.'),
    ];
    $element['link'] = [
      '#prefix' => $this->t('Start typing the title of a piece of content to select it. You can also enter an internal path such as %internal or an external URL such as %external. Enter %front to link to the front page.', [
        '%internal' => '/node/add',
        '%external' => 'https://example.com',
        '%front' => '<front>',
      ]),
      '#type' => 'utexas_link_element',
      '#default_value' => [
        'url' => $items[$delta]->link ?? '',
      ],
      '#suppress_display' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through (potential) field instances.
    foreach ($values as &$value) {
      if (empty($value['image'])) {
        // A null media value should be saved as 0
        $value['image'] = 0;
      }
      // We only want the 'url' part of the link for image link.
      $value['link'] = $value['link']['url'] ?? '';
    }
    return $values;
  }

}
