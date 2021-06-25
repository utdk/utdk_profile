<?php

namespace Drupal\utexas_flex_list\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'utexas_flex_list' widget.
 *
 * @FieldWidget(
 *   id = "utexas_flex_list",
 *   label = @Translation("Flex list"),
 *   field_types = {
 *     "utexas_flex_list"
 *   }
 * )
 */
class UTexasFlexList extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['header'] = [
      '#title' => 'Item header',
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->header) ? $items[$delta]->header : NULL,
      '#size' => '60',
      '#placeholder' => '',
      '#maxlength' => 255,
    ];
    $element['content'] = [
      '#title' => 'Item content',
      '#type' => 'text_format',
      '#default_value' => isset($items[$delta]->content_value) ? $items[$delta]->content_value : NULL,
      '#format' => $items[$delta]->content_format,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      // Split the "text_format" form element data into our field's schema.
      $value['content_value'] = $value['content']['value'];
      $value['content_format'] = $value['content']['format'];
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   *
   * @see Drupal\text\Plugin\Field\FieldWidget\TextareaWithSummaryWidget.php
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    $element = parent::errorElement($element, $violation, $form, $form_state);
    return ($element === FALSE) ? FALSE : $element[$violation->arrayPropertyPath[0]];
  }

}
