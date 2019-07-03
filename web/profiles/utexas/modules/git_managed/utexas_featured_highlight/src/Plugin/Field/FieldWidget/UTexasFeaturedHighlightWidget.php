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
    $element['media'] = [
      '#type' => 'media_library_element',
      '#target_bundles' => ['utexas_image', 'utexas_video_external'],
      '#delta' => $delta,
      '#cardinality' => 1,
      '#title' => t('Media'),
      '#default_value' => isset($items[$delta]->media) ? $items[$delta]->media : 0,
    ];
    $element['headline'] = [
      '#title' => 'Headline',
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->headline) ? $items[$delta]->headline : NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    $element['copy'] = [
      '#title' => 'Copy',
      '#type' => 'text_format',
      '#default_value' => isset($items[$delta]->copy_value) ? $items[$delta]->copy_value : NULL,
      '#format' => $items[$delta]->copy_format ?? 'restricted_html',
    ];
    $element['date'] = [
      '#title' => 'Date',
      '#type' => 'date',
      '#default_value' => isset($items[$delta]->date) ? $items[$delta]->date : NULL,
    ];
    $element['link'] = [
      '#prefix' => $this->t('Start typing the title of a piece of content to select it. You can also enter an internal path such as %internal or an external URL such as %external. Enter %front to link to the front page.', [
        '%internal' => '/node/add',
        '%external' => 'https://example.com',
        '%front' => '<front>',
      ]),
      '#type' => 'utexas_link_element',
      '#default_value' => [
        'url' => $items[$delta]->link_uri ?? '',
        'title' => $items[$delta]->link_text ?? '',
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
      if (isset($value['media']['media_library_selection'])) {
        // @see MediaLibraryElement.php
        $value['media'] = $value['media']['media_library_selection'];
      }
      else {
        $value['image'] = 0;
      }
      if (isset($value['link']['url'])) {
        $value['link_uri'] = $value['link']['url'] ?? '';
        $value['link_text'] = $value['link']['title'] ?? '';
      }
      // Split the "text_format" form element data into our field's schema.
      $value['copy_value'] = $value['copy']['value'];
      $value['copy_format'] = $value['copy']['format'];
    }
    return $values;
  }

}
