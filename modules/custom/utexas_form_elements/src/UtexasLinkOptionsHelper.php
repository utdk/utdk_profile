<?php

namespace Drupal\utexas_form_elements;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Link;
use Drupal\Core\Url;
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
   * @param Drupal\link\LinkItemInterface|null $item
   *   The link item.
   *
   * @return array
   *   The link element with options added.
   */
  public function addLinkOptions(array $element, LinkItemInterface $item = NULL) {
    // Add validation for the two listed elements to the parent element.
    $element['#element_validate'][] = [get_called_class(), 'validateLinkOptionsTarget'];
    $element['#element_validate'][] = [get_called_class(), 'validateLinkOptionsClass'];

    // Handle target attribute form element.
    $target_options = [
      '_blank' => $this->t('Open in new window/tab'),
    ];
    $element['options']['attributes']['target'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Link Behavior'),
      '#options' => $target_options,
      '#attributes' => [
        'class' => [
          'utexas-link-options-attributes-target',
        ],
      ],
      '#access' => isset($element['#suppress_options_target_display']) ? FALSE : TRUE,
    ];

    // Handle lock icons using class attribute form element.
    $class_options = [
      '0' => $this->t('No icon'),
      'ut-cta-link--lock' => $this->t('Authentication required icon <span class="ut-cta-link--lock"></span>'),
      'ut-cta-link--external' => $this->t('External link icon <span class="ut-cta-link--external"></span>'),
    ];
    $element['options']['attributes']['class'] = [
      '#type' => 'radios',
      '#title' => $this->t('Link Appearance'),
      '#options' => $class_options,
      '#access' => isset($element['#suppress_options_class_display']) ? FALSE : TRUE,
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
    // When a widget calls this method, we use the $item to access an 'options'
    // object. When another form element calls this method, we can access the
    // 'options' array directly.
    $options = isset($item) ? $item->get('options')->getValue() : $element['#default_value']['options'];

    $default_target_value = !empty($options['attributes']['target']) ? $options['attributes']['target'] : [];
    $default_class_value = !empty($options['attributes']['class']) ? $options['attributes']['class'] : '0';

    $element['options']['attributes']['target']['#default_value'] = $default_target_value;
    $element['options']['attributes']['class']['#default_value'] = $default_class_value;

    return $element;
  }

  /**
   * Helper function to construct a link with options.
   *
   * @param array $item
   *   The field item link array.
   * @param array $link_type_class
   *   Classes to add to the link (e.g. 'ut-btn', 'ut-link--darker').
   * @param string $link_title_override
   *   Override the link title using a value not in the $item object.
   *
   * @return object
   *   The prepared link object.
   */
  public static function buildLink(array $item, array $link_type_class, string $link_title_override = NULL) {
    // If no uri, return null.
    if (empty($item['link']['uri'])) {
      return NULL;
    }

    // Get the link url.
    $link_url = Url::fromUri($item['link']['uri']);

    // Override the link title text if need be.
    if ($link_title_override) {
      $link_title = $link_title_override;
    }
    elseif (isset($item['link']['title'])) {
      $link_title = $item['link']['title'];
    }

    // Ensure that links without title text print the URL.
    if (empty($link_title)) {
      $url = Url::fromUri($item['link']['uri']);
      $url->setAbsolute();
      $link_title = $url->toString();
    }

    // Note that $link_options['attributes']['class'] may only hold one value
    // (string) when we start here. It is converted to an array if needed.
    $link_options = $item['link']['options'] ?? [];
    $link_options_classes = (array) ($link_options['attributes']['class'] ?? []);

    $merged_link_options_classes = array_merge($link_options_classes, $link_type_class);
    $link_options['attributes']['class'] = $merged_link_options_classes;

    // Set link options and create link.
    $link_url->setOptions($link_options);
    $link = Link::fromTextAndUrl($link_title, $link_url);

    return $link;
  }

  /**
   * Form element validation handler for the 'options' => 'target' element.
   *
   * Requires the URL value if an options target was filled in.
   */
  public static function validateLinkOptionsTarget(&$element, FormStateInterface $form_state, $form) {
    if ($element['uri']['#value'] === '' && $element['options']['attributes']['target']['#value'] !== []) {
      $form_state->setError($element['options']['attributes']['target'], t('The @uri field is required when the @target field is specified.', ['@target' => $element['options']['attributes']['target']['#title'], '@uri' => $element['uri']['#title']]));
    }
  }

  /**
   * Form element validation handler for the 'options' => 'class' element.
   *
   * Requires the URL value if an options class was filled in.
   */
  public static function validateLinkOptionsClass(&$element, FormStateInterface $form_state, $form) {
    if ($element['uri']['#value'] === '' && $element['options']['attributes']['class']['#value'] !== '0') {
      $form_state->setError($element['options']['attributes']['class'], t('The @uri field is required when the @class field is specified.', ['@class' => $element['options']['attributes']['class']['#title'], '@uri' => $element['uri']['#title']]));
    }
  }

}
