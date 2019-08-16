<?php

namespace Drupal\utexas_flex_content_area\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Plugin implementation of the 'utexas_flex_content_area' widget.
 *
 * @FieldWidget(
 *   id = "utexas_flex_content_area",
 *   label = @Translation("Flex Content Area"),
 *   field_types = {
 *     "utexas_flex_content_area"
 *   }
 * )
 */
class UTexasFlexContentAreaWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $element['flex_content_area'] = [
      '#type' => 'details',
      '#title' => $this->t('Flex Content Area %number', ['%number' => $delta + 1]),
    ];
    $element['flex_content_area']['image'] = [
      '#type' => 'media_library_element',
      '#description' => $this->t('If using an image, note that it will be scaled and cropped to 3:2 ratio. Ideally, upload an image of 1000x666 pixels to maintain resolution & avoid cropping.'),
      '#target_bundles' => ['utexas_image', 'utexas_video_external'],
      '#delta' => $delta,
      '#cardinality' => 1,
      '#title' => $this->t('Media'),
      '#default_value' => isset($items[$delta]->image) ? $items[$delta]->image : 0,
    ];
    $element['flex_content_area']['headline'] = [
      '#title' => 'Headline',
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->headline) ? $items[$delta]->headline : NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    $element['flex_content_area']['copy'] = [
      '#title' => 'Copy',
      '#type' => 'text_format',
      '#default_value' => isset($items[$delta]->copy_value) ? $items[$delta]->copy_value : NULL,
      '#format' => $items[$delta]->copy_format ?? 'restricted_html',
    ];
    // Retrieve the form element that is using this widget.
    $parents = [$field_name, 'widget'];
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);
    // This value is defined/leveraged by ::utexasAddMoreSubmit().
    $link_count = isset($widget_state[$field_name][$delta]["counter"]) ? $widget_state[$field_name][$delta]["counter"] : NULL;
    // We have to ensure that there is at least one link field.
    $links = unserialize($items[$delta]->links);
    if ($link_count === NULL) {
      if (empty($links)) {
        $link_count = 1;
      }
      else {
        $link_count = count($links);
      }
      $widget_state[$field_name][$delta]["counter"] = $link_count;
      static::setWidgetState($parents, $field_name, $form_state, $widget_state);
    }
    $wrapper_id = Html::getUniqueId('ajax-wrapper');
    $element['flex_content_area']['links'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('List of links'),
      '#description' => $this->t("Start typing the title of a piece of content to select it. You can also enter an internal path such as %internal or an external URL such as %external. Enter %front to link to the front page.", [
        '%internal' => '/node/add',
        '%external' => 'https://example.com',
        '%front' => '<front>',
      ]),
    ];
    $element['flex_content_area']['links']['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['flex_content_area']['links']['#suffix'] = '</div>';
    for ($i = 0; $i < $link_count; $i++) {
      $element['flex_content_area']['links'][$i] = [
        '#type' => 'utexas_link_element',
        '#default_value' => [
          'url' => $links[$i]['url'] ?? '',
          'title' => $links[$i]['title'] ?? '',
        ],
      ];
    }
    // We limit form validation so that other elements are not validated
    // during this submit button's refresh action. See
    // See https://www.drupal.org/project/drupal/issues/2476569
    $element['flex_content_area']['links']['actions']['add_link'] = [
      '#type' => 'submit',
      '#name' => $field_name . $delta,
      '#value' => $this->t('Add link'),
      '#submit' => [[get_class($this), 'utexasAddMoreSubmit']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [get_class($this), 'utexasAddMoreAjax'],
        'wrapper' => $wrapper_id,
      ],
    ];
    $element['flex_content_area']['cta_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Call to Action'),
    ];
    $element['flex_content_area']['cta_wrapper']['link'] = [
      '#suffix' => $this->t('<div class="description">Start typing the title of a piece of content to select it. You can also enter an internal path such as %internal or an external URL such as %external. Enter %front to link to the front page.</div>', [
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
   * Helper function to extract the add more parent element.
   */
  public static function retrieveAddMoreElement($form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $parents = array_slice($triggering_element['#array_parents'], 0, -2);
    return NestedArray::getValue($form, $parents);
  }

  /**
   * Submission handler for the "Add another item" button.
   */
  public static function utexasAddMoreSubmit(array $form, FormStateInterface $form_state) {
    $element = self::retrieveAddMoreElement($form, $form_state);
    array_pop($element['#parents']);
    array_pop($element['#parents']);
    // The field_delta will be the last (nearest) element in the #parents array.
    $field_delta = array_pop($element['#parents']);
    // The field_name will be the penultimate element in the #parents array.
    $field_name = array_pop($element['#parents']);
    $parents = [$field_name, 'widget'];
    // Increment the items count.
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);
    $widget_state[$field_name][$field_delta]["counter"]++;
    static::setWidgetState($parents, $field_name, $form_state, $widget_state);
    $form_state
      ->setRebuild();
  }

  /**
   * Callback for ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the items in it.
   */
  public static function utexasAddMoreAjax(array &$form, FormStateInterface $form_state) {
    return self::retrieveAddMoreElement($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through (potential) field instances.
    $return = [];
    foreach ($values as $delta => $wrapper) {
      $value = $wrapper['flex_content_area'];
      if (isset($value['image']['media_library_selection'])) {
        // @see MediaLibraryElement.php
        $value['image'] = $value['image']['media_library_selection'];
      }
      else {
        $value['image'] = 0;
      }
      // Links are stored as a serialized array.
      if (!empty($value['links'])) {
        foreach ($value['links'] as $key => $link) {
          if (empty($link['url'])) {
            // Remove empty links.
            unset($value['links'][$key]);
          }
        }
        // Don't serialize an empty array.
        if (!empty($value['links'])) {
          $value['links'] = serialize($value['links']);
        }
        else {
          unset($value['links']);
        }
      }
      if (isset($value['cta_wrapper']['link']['url'])) {
        $value['link_uri'] = $value['cta_wrapper']['link']['url'] ?? '';
        $value['link_text'] = $value['cta_wrapper']['link']['title'] ?? '';
      }
      $value['copy_value'] = $value['copy']['value'];
      $value['copy_format'] = $value['copy']['format'];
      $return[$delta] = $value;
    }
    return $return;
  }

}
