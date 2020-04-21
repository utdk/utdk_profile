<?php

namespace Drupal\utexas_form_elements\Plugin\Field\FieldWidget;

use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget as BaseOptionsButtonsWidget;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alters the default 'options_buttons' widget.
 *
 * @FieldWidget(
 *   id = "options_buttons",
 *   label = @Translation("Check boxes/radio buttons"),
 *   field_types = {
 *     "boolean",
 *     "entity_reference",
 *     "list_integer",
 *     "list_float",
 *     "list_string",
 *   },
 *   multiple_values = TRUE
 * )
 */
class OptionsButtonsWidget extends BaseOptionsButtonsWidget {

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, AccountProxy $current_user) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $type = $items->getFieldDefinition()->getType();
    $settings = $items->getFieldDefinition()->getSettings();
    if ($type == 'entity_reference' && $settings['target_type'] == 'taxonomy_term') {
      $prepared_description = [];
      $prepared_description[] = $element['#description'];
      $options = $this->getOptions($items->getEntity());
      if (!$this->required) {
        array_unshift($prepared_description, $this->t('Optional.'));
      }
      // Handle either single or multiple select none options.
      if (array_keys($options) == ['_none'] || empty($options)) {
        $prepared_description[] = $this->t('<strong>No terms currently exist.</strong>');
      }
      $term_text = $this->t('manage taxonomy terms');
      if ($this->currentUser->hasPermission('administer taxonomy')) {
        $url = Url::fromRoute('entity.taxonomy_vocabulary.collection', []);
        $term_text = Link::fromTextAndUrl($term_text, $url)->toString();
      }
      $prepared_description[] = $this->t('Users with the appropriate permissions can @manage.', ['@manage' => $term_text]);
      $element['#description'] = implode(' ', $prepared_description);
    }
    return $element;
  }

}
