<?php

namespace Drupal\utexas_featured_highlight\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'utexas_featured_highlight' widget.
 *
 * @FieldWidget(
 *   id = "utexas_featured_highlight",
 *   label = @Translation("Featured Highlight"),
 *   field_types = {
 *     "utexas_featured_highlight"
 *   }
 * )
 */
class UTexasFeaturedHighlightWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Get the form item that this widget is being applied to.
    /** @var \Drupal\link\LinkItemInterface $item */
    $item = $items[$delta];

    $element['media'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => ['utexas_image', 'utexas_video_external'],
      '#delta' => $delta,
      '#description' => '',
      '#cardinality' => 1,
      '#title' => $this->t('Media'),
      '#default_value' => isset($item->media) ? $item->media : 0,
    ];
    $element['headline'] = [
      '#title' => 'Headline',
      '#type' => 'textfield',
      '#default_value' => isset($item->headline) ? $item->headline : NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    $element['copy'] = [
      '#title' => 'Copy',
      '#type' => 'text_format',
      '#default_value' => isset($item->copy_value) ? $item->copy_value : NULL,
      '#format' => $item->copy_format ?? 'restricted_html',
    ];
    $element['date'] = [
      '#title' => 'Date',
      '#type' => 'date',
      '#default_value' => isset($item->date) ? $item->date : NULL,
    ];
    $element['cta_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Call to Action'),
    ];
    $element['cta_wrapper']['link'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => isset($item->link_uri) ? $item->link_uri : NULL,
        'title' => isset($item->link_text) ? $item->link_text : NULL,
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
      if (empty($value['date'])) {
        unset($value['date']);
      }

      // A null media value should be saved as 0.
      if (empty($value['media'])) {
        $value['media'] = 0;
      }

      // A null headline value should be removed so that the twig template
      // can easily check for an empty value.
      if (empty($value['headline'])) {
        unset($value['headline']);
      }

      if (isset($value['cta_wrapper']['link']['uri'])) {
        $value['link_uri'] = $value['cta_wrapper']['link']['uri'];
        $value['link_text'] = $value['cta_wrapper']['link']['title'] ?? '';
        $value['link_options'] = $value['cta_wrapper']['link']['options'] ?? [];
      }
      // Split the "text_format" form element data into our field's schema.
      $value['copy_value'] = $value['copy']['value'];
      $value['copy_format'] = $value['copy']['format'];
    }

    return $values;
  }

}
