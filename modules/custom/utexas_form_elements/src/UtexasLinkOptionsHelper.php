<?php

namespace Drupal\utexas_form_elements;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\link\LinkItemInterface;

/**
 * Business logic for rendering the listing view.
 */
class UtexasLinkOptionsHelper {
  use StringTranslationTrait;

  /**
   * Helper function to add link options form elements.
   *
   * @param array $element
   *   The element array.
   * @param Drupal\link\LinkItemInterface $item
   *   The link item.
   *
   * @return array
   *   The link element with options added.
   */
  public function addLinkOptions(array $element, LinkItemInterface $item = NULL) {
    // Handle target attribute form element.
    $target_options = [
      '_blank' => $this->t('Open in new window'),
    ];
    $element['options']['attributes']['target'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Link Behavior'),
      '#options' => $target_options,
    ];

    // Handle lock icons using class attribute form element.
    $class_options = [
      '0' => $this->t('No icon'),
      'ut-cta-link--lock' => $this->t('Authentication required icon'),
      'ut-cta-link--external' => $this->t('External link icon'),
    ];
    $element['options']['attributes']['class'] = [
      '#type' => 'radios',
      '#title' => $this->t('Link Appearance'),
      '#options' => $class_options,
      '#attributes' => [
        'class' => [
          'utexas-link-options-attributes-class',
        ],
      ],
    ];

    $element['#attached']['library'][] = 'utexas_form_elements/link-options';

    // Add defaults. Different methods of default assignment are needed for
    // widgets vs. form elements.
    $element = $this->addLinkOptionsDefaults($element, $item);

    return $element;
  }

  /**
   * Helper function to add defaults to link options.
   *
   * @param array $element
   *   The element array.
   * @param Drupal\link\LinkItemInterface|null $item
   *   The link item.
   *
   * @return array
   *   The link element with link option defaults added.
   */
  private function addLinkOptionsDefaults(array $element, LinkItemInterface $item = NULL) {
    if ($item === NULL) {
      return $element;
    }

    // Get "options" key for access to "attributes" key storage.
    $options = $item->get('options')->getValue();

    // Handle target attribute default value.
    $default_value = !empty($options['attributes']['target']) ? $options['attributes']['target'] : [];
    $element['options']['attributes']['target']['#default_value'] = $default_value;

    // Handle class attribute default value.
    $default_value = !empty($options['attributes']['class']) ? $options['attributes']['class'] : '0';
    $element['options']['attributes']['class']['#default_value'] = $default_value;

    return $element;
  }

}
