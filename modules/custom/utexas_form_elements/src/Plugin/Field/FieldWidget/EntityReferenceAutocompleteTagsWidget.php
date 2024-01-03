<?php

namespace Drupal\utexas_form_elements\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteTagsWidget as BaseAutocompleteWidget;
use Drupal\utexas_form_elements\Traits\TaxonomyDescriptionTrait;

/**
 * Alters the default 'entity_reference_autocomplete_tags' widget.
 *
 * @FieldWidget(
 *   id = "entity_reference_autocomplete_tags",
 *   label = @Translation("Autocomplete (Tags style)"),
 *   description = @Translation("An autocomplete text field with tagging support."),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class EntityReferenceAutocompleteTagsWidget extends BaseAutocompleteWidget {

  use TaxonomyDescriptionTrait;

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $type = $items->getFieldDefinition()->getType();
    $settings = $items->getFieldDefinition()->getSettings();
    if ($type == 'entity_reference' && $settings['target_type'] == 'taxonomy_term') {
      $element['target_id'] = $this->addDynamicTaxonomyDescription($element['target_id'], $items);
      $element['target_id']['#description'] .= ' Separate multiple tags by comma.';
    }
    return $element;
  }

}
