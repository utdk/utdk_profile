<?php

namespace Drupal\utexas_promo_unit\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\utexas_media_types\MediaEntityImageHelper;

/**
 * Defines an element with image, headline, copy and single link.
 *
 * @FormElement("utexas_promo_unit")
 */
class UTexasPromoUnitElement extends FormElement {

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
      '#type' => 'media_library',
      '#allowed_bundles' => MediaEntityImageHelper::getAllowedBundles(),
      '#cardinality' => 1,
      '#name' => 'image',
      '#title' => t('Image'),
      '#default_value' => MediaEntityImageHelper::checkMediaExists($element['#default_value']['image']),
      '#description' => t('Upload an image with aspect ratio appropriate to the view mode you select (see below). To accommodate high resolution screens, images should have dimensions of at least 440 pixels.'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://promo_unit_items/',
    ];
    $element['copy'] = [
      '#title' => 'Copy',
      '#type' => 'text_format',
      '#default_value' => isset($element['#default_value']['copy_value']) ? $element['#default_value']['copy_value'] : NULL,
      '#format' => isset($element['#default_value']['copy_format']) ? $element['#default_value']['copy_format'] : 'restricted_html',
    ];
    $element['link'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => $element['#default_value']['link']['uri'] ?? '',
        'title' => $element['#default_value']['link']['title'] ?? '',
        'options' => $element['#default_value']['link']['options'] ?? [],
      ],
    ];
    $element['#attached']['library'][] = 'utexas_promo_unit/promo-unit-widget';
    return $element;
  }

}
