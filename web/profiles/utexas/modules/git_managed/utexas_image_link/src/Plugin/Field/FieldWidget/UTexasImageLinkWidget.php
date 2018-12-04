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
    $validators = [
      'file_validate_extensions' => ['jpg jpeg png gif'],
    ];
    $element['image'] = [
      '#type' => 'managed_file',
      '#title' => t('Image'),
      '#default_value' => isset($items[$delta]->image) ? [$items[$delta]->image] : 0,
      '#description' => t('This image will fill the width of the region it is placed in.'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://image_link/',
    ];
    $element['link'] = [
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
      if (isset($value['image'][0])) {
        // The image link compound field only ever accepts 1 image,
        // So we store the 0th element from the managed_file array, if present.
        $value['image'] = $value['image'][0];
      }
      else {
        $value['image'] = 0;
      }
      // We only want the 'url' part of the link for image link.
      $value['link'] = $value['link']['url'] ?? '';
    }
    return $values;
  }

}
