<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Plugin implementation of the 'utexas_promo_unit' widget.
 *
 * @FieldWidget(
 *   id = "utexas_promo_unit",
 *   label = @Translation("Promo Unit"),
 *   field_types = {
 *     "utexas_promo_unit"
 *   }
 * )
 */
class UTexasPromoUnitWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $element['headline'] = [
      '#title' => 'Promo Unit Headline',
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->headline) ? $items[$delta]->headline : NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];

    // Gather the number of links in the form already.
    $items = unserialize($items[$delta]->promo_unit_items);
    // Retrieve the form element that is using this widget.
    $parents = [$field_name, 'widget'];
    $widget_state = static::getWidgetState($parents, $field_name, $form_state);
    // This value is defined/leveraged by ::utexasAddMoreSubmit().
    $item_count = isset($widget_state[$field_name][$delta]["counter"]) ? $widget_state[$field_name][$delta]["counter"] : NULL;
    // We have to ensure that there is at least one link field.
    if ($item_count === NULL) {
      if (empty($items)) {
        $item_count = 1;
      }
      else {
        $item_count = count($items);
      }
      $widget_state[$field_name][$delta]["counter"] = $item_count;
      static::setWidgetState($parents, $field_name, $form_state, $widget_state);
    }
    $wrapper_id = Html::getUniqueId('ajax-wrapper');
    $element['promo_unit_items']['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['promo_unit_items']['#suffix'] = '</div>';
    for ($i = 0; $i < $item_count; $i++) {
      $element['promo_unit_items'][$i] = [
        '#type' => 'fieldset',
      ];
      $element['promo_unit_items'][$i]['item'] = [
        '#type' => 'utexas_promo_unit',
        '#default_value' => [
          'headline' => $items[$i]['item']['headline'] ?? '',
          'image' => $items[$i]['item']['image'] ?? '',
          'copy_value' => $items[$i]['item']['copy']['value'] ?? '',
          'copy_format' => $items[$i]['item']['copy']['format'] ?? 'restricted_html',
          'link' => $items[$i]['item']['link'] ?? '',
        ],
      ];
    }
    // We limit form validation so that other elements are not validated
    // during this submit button's refresh action. See
    // See https://www.drupal.org/project/drupal/issues/2476569
    $element['promo_unit_items']['actions']['add'] = [
      '#type' => 'submit',
      '#name' => $field_name . $delta,
      '#value' => $this->t('Add promo unit item'),
      '#submit' => [[get_class($this), 'utexasAddMoreSubmit']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [get_class($this), 'utexasAddMoreAjax'],
        'wrapper' => $wrapper_id,
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through field instances (not link instances).
    foreach ($values as &$value) {
      // Links are stored as a serialized array.
      if (!empty($value['promo_unit_items'])) {
        foreach ($value['promo_unit_items'] as $key => $item) {
          if (empty($item['item']['headline'])
          && empty($item['item']['image'])
          && empty($item['item']['copy']['value'])
          && empty($item['item']['link'])) {
            // Remove empty links.
            unset($value['promo_unit_items'][$key]);
          }
          else {
            if (isset($item['item']['image']['media_library_selection'])) {
              // @see MediaLibraryElement.php
              $value['promo_unit_items'][$key]['item']['image'] = $item['item']['image']['media_library_selection'];
            }
          }
        }
        if (!empty($value['promo_unit_items'])) {
          $value['promo_unit_items'] = serialize($value['promo_unit_items']);
        }
        else {
          unset($value['promo_unit_items']);
        }
      }
    }

    return $values;
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
    $wrapper = array_pop($element['#parents']);
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

}
