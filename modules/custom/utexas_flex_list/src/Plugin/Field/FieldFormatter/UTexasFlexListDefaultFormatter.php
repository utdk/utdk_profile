<?php

namespace Drupal\utexas_flex_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;

/**
 * Plugin implementation of the 'utexas_flex_list' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_flex_list_default",
 *   label = @Translation("Flex list"),
 *   field_types = {
 *     "utexas_flex_list"
 *   }
 * )
 */
class UTexasFlexListDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'heading_level' => 'h5',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['heading_level'] = [
      '#type' => 'select',
      '#title' => $this->t('Heading level'),
      '#options' => [
        'h2' => $this->t('h2'),
        'h3' => $this->t('h3'),
        'h4' => $this->t('h4'),
        'h5' => $this->t('h5'),
        'h6' => $this->t('h6'),
      ],
      '#default_value' => $this->getSetting('heading_level'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();
    if (!empty($settings['heading_level'])) {
      $summary[] = t('Heading level: @display', ['@display' => $settings['heading_level']]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $settings = $this->getSettings();
    $element = [
      '#attributes' => [
        'class' => [
          'utexas-flex-list',
        ],
      ],
    ];
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#theme' => 'utexas_flex_list',
        '#heading_level' => $settings['heading_level'],
        '#header' => $item->header,
        '#id' => Html::getUniqueId($item->header),
        '#content' => check_markup($item->content_value, $item->content_format),
      ];
      $element['#attached']['library'][] = 'utexas_flex_list/base';
    }
    return $element;
  }

}
