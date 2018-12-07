<?php

namespace Drupal\utexas_promo_unit\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an element with image, headline, copy and single link.
 *
 * @FormElement("utexas_promo_unit")
 */
class UtexasPromoUnitElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#tree' => TRUE,
      '#process' => [
        [$class, 'processPromoUnitElement'],
      ],
    ];
  }

  /**
   * Process handler for the link form element.
   */
  public static function processPromoUnitElement(&$element, FormStateInterface $form_state, &$form) {
    $validators = [
      'file_validate_extensions' => ['jpg jpeg png gif'],
    ];
    $element['headline'] = [
      '#type' => 'textfield',
      '#title' => t('Item Headline'),
      '#default_value' => isset($element['#default_value']['headline']) ? $element['#default_value']['headline'] : '',
    ];
    $element['image'] = [
      '#type' => 'managed_file',
      '#name' => 'image',
      '#title' => t('Image'),
      '#default_value' => isset($element['#default_value']['image']) ? $element['#default_value']['image'] : 0,
      '#description' => t('Image will be scaled and cropped to a 1:1 ratio. Ideally, upload an image of 170x170 pixels to maintain resolution & avoid cropping.'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://promo_unit_items/',
    ];
    $element['copy'] = [
      '#title' => 'Copy',
      '#type' => 'text_format',
      '#default_value' => isset($element['#default_value']['copy_value']) ? $element['#default_value']['copy_value'] : NULL,
      '#format' => isset($element['#default_value']['copy_format']) ? $element['#default_value']['copy_format'] : 'flex_html',
    ];
    $element['link'] = [
      '#type' => 'utexas_link_element',
      '#default_value' => [
        'url' => $element['#default_value']['link']['url'] ?? '',
        'title' => $element['#default_value']['link']['title'] ?? '',
      ],
    ];

    return $element;
  }

}
