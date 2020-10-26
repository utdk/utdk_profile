<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'utexas_hero' widget.
 *
 * @FieldWidget(
 *   id = "utexas_hero",
 *   label = @Translation("Hero"),
 *   field_types = {
 *     "utexas_hero"
 *   }
 * )
 */
class UTexasHeroWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    // Get the form item that this widget is being applied to.
    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];
    $element['media'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => ['utexas_image'],
      '#delta' => $delta,
      '#cardinality' => 1,
      '#title' => $this->t('Image'),
      '#default_value' => isset($item->media) ? $item->media : NULL,
      '#description' => $this->t('Image will be scaled and cropped to a 87:47 ratio. Upload an image with a minimum resolution of 2280x1232 pixels to maintain quality and avoid cropping.'),
    ];
    $element['disable_image_styles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable image size optimization.'),
      '#description' => $this->t('Check this if you need to display an animated GIF or have specific image dimensions requirements.'),
      '#default_value' => $item->disable_image_styles ?? 0,
      '#states' => [
        'invisible' => [
          ':input[name="' . $field_name . '[' . $delta . '][media][media_library_selection]"]' => ['value' => "0"],
        ],
      ],
    ];
    $element['heading'] = [
      '#title' => $this->t('Heading'),
      '#type' => 'textfield',
      '#default_value' => isset($item->heading) ? $item->heading : NULL,
      '#size' => '60',
      '#description' => $this->t('Optional, but recommended to provide alternative textual explanation of the media.'),
      '#maxlength' => 255,
    ];
    $element['subheading'] = [
      '#title' => $this->t('Subheading'),
      '#type' => 'textfield',
      '#default_value' => isset($item->subheading) ? $item->subheading : NULL,
      '#size' => '60',
      '#description' => $this->t('Optional. Displays directly beneath the heading. For best appearance, use no more than 140 characters. Note: this field is not visible in the default display or in hero style 2.'),
      '#maxlength' => 255,
    ];
    $element['caption'] = [
      '#title' => $this->t('Caption'),
      '#type' => 'textfield',
      '#default_value' => isset($item->subheading) ? $item->caption : NULL,
      '#size' => '60',
      '#description' => $this->t('Optional text to display directly beneath the media.'),
      '#maxlength' => 255,
    ];
    $element['credit'] = [
      '#title' => $this->t('Credit'),
      '#type' => 'textfield',
      '#default_value' => isset($item->subheading) ? $item->credit : NULL,
      '#size' => '60',
      '#description' => $this->t('Optional way to provide attribution, displayed directly beneath the media.'),
      '#maxlength' => 255,
    ];
    $element['cta'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Call to Action'),
    ];
    $element['cta']['link'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => $item->link_uri ?? '',
        'title' => $item->link_title ?? '',
        'options' => isset($item->link_options) ? $item->link_options : [],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through (potential) field instances.
    foreach ($values as &$value) {
      if (empty($value['media'])) {
        // A null media value should be saved as 0.
        $value['media'] = 0;
      }
      if (isset($value['cta']['link']['uri'])) {
        $value['link_uri'] = $value['cta']['link']['uri'] ?? '';
        $value['link_title'] = $value['cta']['link']['title'] ?? '';
        $value['link_options'] = $value['cta']['link']['options'] ?? [];
      }
    }
    return $values;
  }

}
