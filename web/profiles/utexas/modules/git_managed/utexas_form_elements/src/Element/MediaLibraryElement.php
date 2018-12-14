<?php

namespace Drupal\utexas_form_elements\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Component\Utility\NestedArray;
use Drupal\media\Entity\Media;

/**
 * Defines an element for using the media library.
 *
 * @FormElement("media_library_element")
 *
 * Usage can include the following components:
 *
 *   $element['image'] = [
 *     '#type' => 'media_library_element',
 *     '#target_bundles' => [],
 *     '#title' => t(''),
 *     '#default_value' => [],
 *     '#description' => t(''),
 *   ];
 */
class MediaLibraryElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#tree' => TRUE,
      '#process' => [[$class, 'processMediaBrowser']],
      '#default_value' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // Pass the form state value to the widget (see massageFormValues).
    if ($input['media_library_selection']) {
      return $input['media_library_selection'];
    }
    // Populate the form initially from widget default values.
    if ($input === FALSE) {
      return $element['#default_value'] ?: 0;
    }
    // Handle empty field delta.
    return 0;
  }

  /**
   * Process handler for the Media Library element.
   */
  public static function processMediaBrowser(&$element, FormStateInterface $form_state, &$form) {
    $entity_type = 'media';
    $view_mode = 'media_library';

    $field_name = $element['#parents'][0];
    $parents = $form['#parents'];
    // Ensure that fields with multiple deltas have unique wrapper IDs.
    $id_suffix = '-' . implode('-', $element['#parents']);
    // Generate a unique wrapper HTML ID.
    $wrapper_id = Html::getUniqueId('ajax-wrapper');

    $limit_validation_errors = [array_merge($parents, [$field_name])];
    // Prefix and suffix used for Ajax replacement.
    $title = $element['#title'] ? '<strong>' . $element['#title'] . '</strong>' : '';
    $element['#prefix'] = '<div id="' . $wrapper_id . '">' . $title;
    $description = $element['#description'] ? '<br /><em>' . $element['#description'] . '</em>' : '';
    $element['#suffix'] = $description . '</div>';
    $element['#attached']['library'][] = 'media_library/widget';

    $element['selection'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'js-media-library-selection',
          'media-library-selection',
        ],
      ],
    ];
    if ($media_item = \Drupal::entityTypeManager()->getStorage($entity_type)->load($element['#value'])) {
      $element['selection'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'media-library-item',
            'js-media-library-item',
          ],
        ],
        'preview' => [
          '#type' => 'container',
          'rendered_entity' => \Drupal::entityTypeManager()->getViewBuilder($entity_type)->view($media_item, $view_mode),
          'remove_button' => [
            '#type' => 'submit',
            '#name' => $field_name . '-media-library-remove-button' . $id_suffix,
            '#value' => t('Remove'),
            '#attributes' => [
              'class' => ['media-library-item__remove'],
            ],
            '#ajax' => [
              'callback' => [static::class, 'updateWidgetRemove'],
              'wrapper' => $wrapper_id,
              'disable-refocus' => FALSE,
            ],
            '#submit' => [[static::class, 'updateItem']],
            // Prevent errors in other widgets from preventing removal.
            '#limit_validation_errors' => $limit_validation_errors,
          ],
        ],
      ];
    }

    $query = [
      'media_library_widget_id' => $field_name . $id_suffix,
      'media_library_allowed_types' => $element['#target_bundles'] ?? FALSE,
      'media_library_remaining' => 1,
    ];
    $dialog_options = Json::encode([
      'dialogClass' => 'media-library-widget-modal',
      'height' => '75%',
      'width' => '75%',
      'title' => t('Media library'),
    ]);

    // Add a button that will load the Media library in a modal using AJAX.
    $element['media_library_open_button'] = [
      '#type' => 'link',
      '#title' => empty($media_item) ? t('Add media') : t('Replace media'),
      '#name' => $field_name . '-media-library-open-button' . $id_suffix,
      '#url' => Url::fromRoute('view.media_library.widget', [], [
        'query' => $query,
      ]),
      '#attributes' => [
        'class' => ['button', 'use-ajax', 'media-library-open-button'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => $dialog_options,
      ],
      // Prevent errors in other widgets from preventing addition.
      '#limit_validation_errors' => $limit_validation_errors,
      '#access' => TRUE,
    ];

    // This hidden field and button are used to add new item to the widget.
    $element['media_library_selection'] = [
      '#type' => 'hidden',
      '#attributes' => [
        // This is used to pass the selection from the modal to the widget.
        'data-media-library-widget-value' => $field_name . $id_suffix,
      ],
      '#default_value' => $element['#value'],
    ];

    // When a selection is made this hidden button is pressed to add new media
    // item based on the "media_library_selection" value.
    $element['media_library_update_widget'] = [
      '#type' => 'submit',
      '#value' => t('Update widget'),
      '#name' => $field_name . '-media-library-update' . $id_suffix,
      '#ajax' => [
        'callback' => [static::class, 'updateWidget'],
        'wrapper' => $wrapper_id,
        'disable-refocus' => TRUE,
      ],
      '#attributes' => [
        'data-media-library-widget-update' => $field_name . $id_suffix,
        'class' => ['js-hide'],
      ],
      '#validate' => [[static::class, 'validateItem']],
      '#submit' => [[static::class, 'updateItem']],
      // Prevent errors in other widgets from preventing updates.
      '#limit_validation_errors' => $limit_validation_errors,
    ];
    return $element;
  }

  /**
   * AJAX callback to update the widget when the selection changes.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   An array representing the updated widget.
   */
  public static function updateWidget(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $length = end($triggering_element['#parents']) === 'remove_button' ? -3 : -1;
    $parents = array_slice($triggering_element['#array_parents'], 0, $length);
    $element = NestedArray::getValue($form, $parents);
    return $element;
  }

  /**
   * AJAX callback to update the widget when the selection changes.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   An array representing the updated widget.
   */
  public static function updateWidgetRemove(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $length = end($triggering_element['#parents']) === 'remove_button' ? -3 : -1;
    $parents = array_slice($triggering_element['#array_parents'], 0, $length);
    $element = NestedArray::getValue($form, $parents);
    $element['media_library_selection']['#value'] = 0;
    $element['media_library_open_button']['#title'] = t('Add media');
    unset($element['selection']);
    return $element;
  }

  /**
   * Updates the field state and flags the form for rebuild.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function updateItem(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
    $form_state->setRebuild();
  }

  /**
   * Validates that newly selected items can be added to the widget.
   *
   * Making an invalid selection from the view should not be possible, but we
   * still validate in case other selection methods (ex: upload) are valid.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function validateItem(array $form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));

    $field_state = static::getFieldState($element, $form_state);
    $media = static::getNewMediaItem($element, $form_state);
    if (empty($media)) {
      return;
    }

    // Validate that each selected media is of an allowed bundle.
    $all_bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('media');
    $bundle_labels = array_map(function ($bundle) use ($all_bundles) {
      return $all_bundles[$bundle]['label'];
    }, $element['#target_bundles']);
    if ($element['#target_bundles'] && !in_array($media->bundle(), $element['#target_bundles'], TRUE)) {
      $form_state->setError($element, t('The media item "@label" is not of an accepted type. Allowed types: @types', [
        '@label' => $media->label(),
        '@types' => implode(', ', $bundle_labels),
      ]));
    }
  }

  /**
   * Gets newly selected media item.
   *
   * @param array $element
   *   The wrapping element for this widget.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\media\MediaInterface[]
   *   An array of selected media item.
   */
  protected static function getNewMediaItem(array $element, FormStateInterface $form_state) {
    // Get the new media IDs passed to our hidden button.
    $values = $form_state->getValues();
    $path = $element['#parents'];
    $value = NestedArray::getValue($values, $path);

    if (!empty($value['media_library_selection'])) {
      /** @var \Drupal\media\MediaInterface[] $media */
      return Media::load($value['media_library_selection']);
    }
    return FALSE;
  }

  /**
   * Gets the field state for the widget.
   *
   * @param array $element
   *   The wrapping element for this widget.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array[]
   *   An array of arrays with the following key/value pairs:
   *   - items: (array) An array of MIDs.
   */
  protected static function getFieldState(array $element, FormStateInterface $form_state) {
    // Default to using the current selection if the form is new.
    $path = $element['#parents'];
    $values = NestedArray::getValue($form_state->getValues(), $path);
    $selection = isset($values['selection']) ? $values['selection'] : [];
    $widget_state = [];
    $widget_state['item'] = isset($widget_state['item']) ? $widget_state['item'] : $selection;
    return $widget_state;
  }

}