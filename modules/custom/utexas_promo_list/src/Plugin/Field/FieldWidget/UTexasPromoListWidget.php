<?php

namespace Drupal\utexas_promo_list\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'utexas_promo_list' widget.
 *
 * @FieldWidget(
 *   id = "utexas_promo_list",
 *   label = @Translation("Promo List"),
 *   field_types = {
 *     "utexas_promo_list"
 *   }
 * )
 */
class UTexasPromoListWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $element['headline'] = [
      '#title' => 'List Headline',
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->headline) ? $items[$delta]->headline : NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    // Gather the number of items in the Promo List.
    $items = !empty($items[$delta]->promo_list_items) ? unserialize($items[$delta]->promo_list_items) : [];
    // Ensure item keys are consecutive.
    $items = array_values($items);
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

    $element['promo_list_items'] = $this->buildDraggableItems($items, $item_count);
    $wrapper_id = Html::getUniqueId('ajax-wrapper');
    $element['promo_list_items']['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['promo_list_items']['#suffix'] = '</div>';
    $element['promo_list_items']['actions']['add'] = [
      '#type' => 'submit',
      '#name' => $field_name . $delta,
      '#value' => $this->t('Add Promo List item'),
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
   * Create a tabledrag container for all Promo List items.
   *
   * @param array $items
   *   Any stored Promo List items.
   * @param int $item_count
   *   Items to be populated. Will change on ajax submit for add more.
   *
   * @return array
   *   A render array of a draggable table of items.
   */
  protected function buildDraggableItems(array $items, $item_count) {
    $group_class = 'group-order-weight';
    // Build table.
    $form['items'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Promo List items'),
        $this->t('Weight'),
      ],
      '#empty' => $this->t('No items.'),
      '#tableselect' => FALSE,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => $group_class,
        ],
      ],
    ];
    // Build rows.
    $weight = 0;
    // Match Drupal core 'show weights' behavior for > 20 items.
    if ($item_count >= 20) {
      $weight = ceil($item_count / 2) * -1;
    }
    for ($i = 0; $i < $item_count; $i++) {
      $form['items'][$i]['#attributes']['class'][] = 'draggable';
      $form['items'][$i]['#weight'] = $weight;
      // Label column.
      $form['items'][$i]['details'] = [
        '#type' => 'details',
        '#title' => $this->t('Promo List item %number %headline', [
          '%number' => $i + 1,
          '%headline' => isset($items[$i]['item']['headline']) ? '(' . $items[$i]['item']['headline'] . ')' : '',
        ]),
      ];
      $form['items'][$i]['details']['item'] = [
        '#type' => 'utexas_promo_list',
        '#default_value' => [
          'headline' => $items[$i]['item']['headline'] ?? '',
          'image' => $items[$i]['item']['image'] ?? '',
          'copy_value' => $items[$i]['item']['copy']['value'] ?? '',
          'copy_format' => $items[$i]['item']['copy']['format'] ?? 'restricted_html',
          'link' => $items[$i]['item']['link'] ?? '',
        ],
      ];
      // Weight column.
      $form['items'][$i]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for Promo List item @key', ['@key' => $weight]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#delta' => ceil($item_count / 2),
        '#attributes' => ['class' => [$group_class]],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $storage = [];
    // Loop through field deltas.
    foreach ($values as $delta => $field) {
      if (isset($field['headline'])) {
        // The overall group headline.
        $storage[$delta]['headline'] = $field['headline'];
      }
      if (isset($field['promo_list_items'])) {
        // Re-sort by the order provided by tabledrag.
        usort($field['promo_list_items']['items'], function ($item1, $item2) {
          return $item1['weight'] <=> $item2['weight'];
        });
        foreach ($field['promo_list_items']['items'] as $weight => $item) {
          $elements = $item['details']['item'];
          $storage[$delta]['promo_list_items'][$weight]['item'] = [];
          if (!empty($elements['headline'])) {
            $storage[$delta]['promo_list_items'][$weight]['item']['headline'] = $elements['headline'];
          }
          if (!empty($elements['image'])) {
            $storage[$delta]['promo_list_items'][$weight]['item']['image'] = $elements['image'];
          }
          if (!empty($elements['copy']['value'])) {
            $storage[$delta]['promo_list_items'][$weight]['item']['copy'] = $elements['copy'];
          }
          if (!empty($elements['link']['uri'])) {
            $storage[$delta]['promo_list_items'][$weight]['item']['link']['uri'] = $elements['link']['uri'];
            $storage[$delta]['promo_list_items'][$weight]['item']['link']['title'] = $elements['link']['title'];
            $storage[$delta]['promo_list_items'][$weight]['item']['link']['options'] = $elements['link']['options'];
          }
          // Remove empty items
          // (i.e., user has manually emptied the field contents).
          if (empty($storage[$delta]['promo_list_items'][$weight]['item'])) {
            unset($storage[$delta]['promo_list_items'][$weight]);
          }
        }
      }
      // If no Promo List items have been added, remove the empty array.
      if (empty($storage[$delta]['promo_list_items'])) {
        unset($storage[$delta]['promo_list_items']);
      }
      else {
        // Promo List items are stored in a serialized array,
        // with consecutive keys.
        $storage[$delta]['promo_list_items'] = serialize(array_values($storage[$delta]['promo_list_items']));
      }
    }
    return $storage;
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
