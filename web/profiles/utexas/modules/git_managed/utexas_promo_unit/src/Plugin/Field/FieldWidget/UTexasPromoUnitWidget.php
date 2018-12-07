<?php

namespace Drupal\utexas_promo_unit\Plugin\Field\FieldWidget;

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
  const ADD_MORE_ELEMENT = 'link-fieldset';

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $fieldset_wrapper_name = self::ADD_MORE_ELEMENT . $field_name . '-' . $delta;
    $element['headline'] = [
      '#title' => 'Promo Unit Headline',
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->headline) ? $items[$delta]->headline : NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    $element[self::ADD_MORE_ELEMENT] = [
      '#type' => 'markup',
      '#title' => $this->t('Promo Unit Items'),
      '#prefix' => '<div id="' . $fieldset_wrapper_name . '">',
      '#suffix' => '</div>',
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
    for ($i = 0; $i < $item_count; $i++) {
      $element[self::ADD_MORE_ELEMENT]['promo_unit_items'][$i] = [
        '#type' => 'fieldset',
      ];
      $element[self::ADD_MORE_ELEMENT]['promo_unit_items'][$i]['item'] = [
        '#type' => 'utexas_promo_unit',
        '#default_value' => [
          'headline' => $items[$i]['item']['headline'] ?? '',
          'image' => $items[$i]['item']['image'] ?? '',
          'copy_value' => $items[$i]['item']['copy']['value'] ?? '',
          'copy_format' => $items[$i]['item']['copy']['format'] ?? 'flex_html',
          'link' => $items[$i]['item']['link'] ?? '',
        ],
      ];
    }
    // We limit form validation so that other elements are not validated
    // during this submit button's refresh action. See
    // See https://www.drupal.org/project/drupal/issues/2476569
    $element[self::ADD_MORE_ELEMENT]['actions']['add'] = [
      '#type' => 'submit',
      '#name' => $field_name . $delta,
      '#value' => $this->t('Add promo unit item'),
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
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // This loop is through field instances (not link instances).
    foreach ($values as &$value) {
      // Links are stored as a serialized array.
      if (!empty($value[self::ADD_MORE_ELEMENT]['promo_unit_items'])) {
        foreach ($value[self::ADD_MORE_ELEMENT]['promo_unit_items'] as $key => $item) {
          if (empty($item['item']['headline'])
          && empty($item['item']['image'])
          && empty($item['item']['copy']['value'])
          && empty($item['item']['link'])) {
            // Remove empty links.
            unset($value[self::ADD_MORE_ELEMENT]['promo_unit_items'][$key]);
          }
          else {
            if (!empty($item['item']['image'][0])) {
              $file = \Drupal::entityTypeManager()->getStorage('file')->load($item['item']['image'][0]);
              if ($file) {
                $file->setPermanent();
                $file->save();
                // @todo: properly manage file usage count.
                // Look up existing node & scan for instances??
                $file_usage = \Drupal::service('file.usage');
                $file_usage->add(
                  $file,
                  'utexas_promo_unit',
                  $this->fieldDefinition->getTargetEntityTypeId(),
                  \Drupal::currentUser()->id()
                );
                $value[self::ADD_MORE_ELEMENT]['promo_unit_items'][$key]['item']['image'] = [$file->id()];
              }
            }
          }
        }
        if (!empty($value[self::ADD_MORE_ELEMENT]['promo_unit_items'])) {
          $value['promo_unit_items'] = serialize($value[self::ADD_MORE_ELEMENT]['promo_unit_items']);
        }
      }
    }

    return $values;
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
   * Selects and returns the fieldset with the items in it.
   */
  public static function utexasAddMoreAjax(array &$form, FormStateInterface $form_state) {
    return self::retrieveAddMoreElement($form, $form_state);
  }

}
