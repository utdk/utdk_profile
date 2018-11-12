<?php

namespace Drupal\utexas_quick_links\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\NestedArray;

/**
 * Plugin implementation of the 'utexas_quick_links' widget.
 *
 * @FieldWidget(
 *   id = "utexas_quick_links",
 *   label = @Translation("UTexas Quick Links"),
 *   field_types = {
 *     "utexas_quick_links"
 *   }
 * )
 */
class UTexasQuickLinksWidget extends WidgetBase {

  const ADD_MORE_ELEMENT = 'link-fieldset';

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $fieldset_wrapper_name = self::ADD_MORE_ELEMENT . $field_name . '-' . $delta;
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
      '#format' => isset($items[$delta]->copy_format) ? $items[$delta]->copy_format : NULL,
    ];
    $element[self::ADD_MORE_ELEMENT] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Quick Links'),
      '#prefix' => '<div id="' . $fieldset_wrapper_name . '">',
      '#suffix' => '</div>',
    ];
    // Gather the number of links in the form already.
    $links = unserialize($items[$delta]->links);
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
    for ($i = 0; $i < $link_count; $i++) {
      $element[self::ADD_MORE_ELEMENT]['links'][$i] = [
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
    $element[self::ADD_MORE_ELEMENT]['actions']['add_link'] = [
      '#type' => 'submit',
      '#name' => $field_name . $delta,
      '#value' => $this->t('Add link'),
      '#submit' => [[get_class($this), 'utexasAddMoreSubmit']],
      '#limit_validation_errors' => [],
      '#ajax' => [
        'callback' => [get_class($this), 'utexasAddMoreAjax'],
        'wrapper' => $fieldset_wrapper_name,
      ],
    ];
    return $element;
  }

  /**
   * Helper function to extract the add more parent element.
   */
  public static function retrieveAddMoreElement($form, FormStateInterface $form_state) {
    // Modelled on WidgetBase::addMoreAjax().
    $button = $form_state->getTriggeringElement();
    foreach ($button['#array_parents'] as $current_parent) {
      $current = (string) $current_parent;
      if ($current == self::ADD_MORE_ELEMENT) {
        $parent_tree[] = self::ADD_MORE_ELEMENT;
        break;
      }
      else {
        $parent_tree[] = $current;
      }
    }
    return NestedArray::getValue($form, $parent_tree);
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
   * Selects and returns the fieldset with the links in it.
   */
  public static function utexasAddMoreAjax(array &$form, FormStateInterface $form_state) {
    return self::retrieveAddMoreElement($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through field instances (not link instances).
    foreach ($values as &$value) {
      // Links are stored as a serialized array.
      if (!empty($value[self::ADD_MORE_ELEMENT]['links'])) {
        foreach ($value[self::ADD_MORE_ELEMENT]['links'] as $key => $link) {
          if (empty($link['url'])) {
            // Remove empty links.
            unset($value[self::ADD_MORE_ELEMENT]['links'][$key]);
          }
        }
        // Don't serialize an empty array.
        if (!empty($value[self::ADD_MORE_ELEMENT]['links'])) {
          $value['links'] = serialize($value[self::ADD_MORE_ELEMENT]['links']);
        }
      }
      // Split the "text_format" form element data into our field's schema.
      $value['copy_value'] = $value['copy']['value'];
      $value['copy_format'] = $value['copy']['format'];
    }
    return $values;
  }

}
