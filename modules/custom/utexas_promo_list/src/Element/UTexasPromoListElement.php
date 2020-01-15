<?php

namespace Drupal\utexas_promo_list\Element;

use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an element for a single link + title field.
 *
 * @FormElement("utexas_promo_list")
 */
class UtexasPromoListElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#tree' => TRUE,
      '#process' => [
        [$class, 'processLinkElement'],
      ],
    ];
  }

  /**
   * Process handler for the link form element.
   */
  public static function processLinkElement(&$element, FormStateInterface $form_state, &$form) {
    $element['headline'] = [
      '#type' => 'textfield',
      '#title' => t('Item Headline'),
      '#default_value' => isset($element['#default_value']['headline']) ? $element['#default_value']['headline'] : '',
    ];
    $element['image'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => ['utexas_image'],
      '#cardinality' => 1,
      '#name' => 'image',
      '#title' => t('Image'),
      '#default_value' => isset($element['#default_value']['image']) ? $element['#default_value']['image'] : 0,
      '#description' => t('Image will be scaled and cropped to a 1:1 ratio. Ideally, upload an image of 170x170 pixels to maintain resolution & avoid cropping.'),
      '#upload_location' => 'public://promo_list_items/',
    ];
    $element['copy'] = [
      '#title' => 'Copy',
      '#type' => 'text_format',
      '#default_value' => isset($element['#default_value']['copy_value']) ? $element['#default_value']['copy_value'] : NULL,
      '#format' => isset($element['#default_value']['copy_format']) ? $element['#default_value']['copy_format'] : 'restricted_html',
    ];
    $element['link'] = [
      '#type' => 'utexas_link_element',
      '#default_value' => [
        'url' => $element['#default_value']['link'] ?? '',
      ],
      '#description' => t('A valid URL for this promo list. If present, the item headline and image will become links. Start typing the title of a piece of content to select it. You can also enter an internal path such as %internal or an external URL such as %external. Enter %front to link to the front page.', [
        '%internal' => '/node/add',
        '%external' => 'https://example.com',
        '%front' => '<front>',
      ]),
      '#suppress_display' => TRUE,
    ];
    return $element;
  }

}
