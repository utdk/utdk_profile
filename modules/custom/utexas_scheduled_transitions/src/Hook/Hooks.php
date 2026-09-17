<?php

namespace Drupal\utexas_scheduled_transitions\Hook;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\node\NodeTypeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\scheduled_transitions\Form\ScheduledTransitionsSettingsForm;
use Drupal\scheduled_transitions\ScheduledTransitionsPermissions;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Sets site defaults for `simplified_ui` and `automation.messenger` settings.
   */
  public function applyScheduledTransitionsSettingsDefaults() {
    $config = \Drupal::configFactory()->getEditable('scheduled_transitions.settings');
    $config
      ->set('simplified_ui.only_latest_revision', TRUE)
      ->set('simplified_ui.restrict_transitions', FALSE)
      ->set('simplified_ui.allowed_transitions', $config->get('simplified_ui.allowed_transitions') ?? [])
      ->set('automation.cron_create_queue_items', FALSE)
      ->set('automation.messenger', FALSE)
      ->save();
    // See the note in registerBundle() about why this direct config save
    // requires an explicit cache tag invalidation.
    \Drupal::service('cache_tags.invalidator')->invalidateTags([ScheduledTransitionsSettingsForm::SETTINGS_TAG]);
  }

  /**
   * Registers a node bundle with Scheduled Transitions and grants access.
   */
  public function registerBundle($bundle) {
    // Skip the featured add-ons. Their install hooks are handled separately.
    $entity_type = 'node';

    $config = \Drupal::configFactory()->getEditable('scheduled_transitions.settings');
    $bundles = $config->get('bundles') ?? [];
    $entry = ['entity_type' => $entity_type, 'bundle' => $bundle];
    if (!in_array($entry, $bundles)) {
      $bundles[] = $entry;
      $config->set('bundles', $bundles)->save();
      // ScheduledTransitionsUtility::getBundles() caches the enabled bundle
      // list under this tag; the settings form invalidates it on save, but a
      // direct config save (as done here) does not, so later dynamic
      // permissions in this request would otherwise be computed from a stale
      // cache and miss the bundle just added.
      \Drupal::service('cache_tags.invalidator')->invalidateTags([ScheduledTransitionsSettingsForm::SETTINGS_TAG]);
    }

    // Generate the new permissions dynamically.
    $permissions = [
      ScheduledTransitionsPermissions::viewScheduledTransitionsPermission($entity_type, $bundle),
      ScheduledTransitionsPermissions::addScheduledTransitionsPermission($entity_type, $bundle),
      ScheduledTransitionsPermissions::rescheduleScheduledTransitionsPermission($entity_type, $bundle),
    ];
    $available_permissions = \Drupal::service('user.permissions')->getPermissions();

    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    /** @var \Drupal\user\Entity\Role $role */
    foreach ($roles as $role) {
      if (!$role->hasPermission("create $bundle content")) {
        continue;
      }
      $granted = FALSE;
      foreach ($permissions as $permission) {
        if (isset($available_permissions[$permission]) && !$role->hasPermission($permission)) {
          $role->grantPermission($permission);
          $granted = TRUE;
        }
      }
      if ($granted) {
        $role->save();
        \Drupal::messenger()->addMessage($this->t('Scheduled transitions permissions set for %role role on %bundle content.', [
          '%role' => $role->label(),
          '%bundle' => $bundle,
        ]));
      }
    }
  }

  /**
   * Implements hook_node_type_insert().
   */
  #[Hook('node_type_insert')]
  public function nodeTypeInsert(NodeTypeInterface $type) {
    $this->registerBundle($type->id());
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    $is_add_form = isset($form['scheduled_transitions']['new_meta']['on']['time']);
    $is_reschedule_form = strpos($form_id, 'scheduled_transition') !== FALSE && isset($form['date']['time']);

    if (!$is_add_form && !$is_reschedule_form) {
      return;
    }

    // Attach the hour-restrict library for client-side behavior.
    $form['#attached']['library'][] = 'utexas_scheduled_transitions/datetime_hour_restrict';

    // Add custom validation handler.
    $form['#validate'][] = [$this, 'validateTime'];

    // Add help text to the appropriate section.
    $help_text = '<div class="form-help-text">' .
      $this->t('Scheduled transitions are only allowed at the top of the hour.') .
      '</div>';

    if ($is_add_form) {
      // Add form structure: help text before the date/time inputs.
      $form['scheduled_transitions']['new_meta']['on']['#suffix'] = $help_text;
    }
    else {
      // Reschedule form structure.
      $form['date']['#suffix'] = $help_text;
    }
  }

  /**
   * Custom validation handler for scheduled transitions form.
   */
  public function validateTime(&$form, FormStateInterface $form_state) {
    // The add form posts 'on' and the reschedule form posts 'date', both
    // top-level (the 'scheduled_transitions' wrapper isn't a #tree element).
    // By the time this runs, element validation has converted the value to
    // a DrupalDateTime.
    if (isset($form['scheduled_transitions']['new_meta']['on'])) {
      $date = $form_state->getValue('on');
      $error_element = $form['scheduled_transitions']['new_meta']['on'];
    }
    elseif (isset($form['date'])) {
      $date = $form_state->getValue('date');
      $error_element = $form['date'];
    }
    else {
      return;
    }

    if (!($date instanceof DrupalDateTime) || $date->hasErrors()) {
      return;
    }

    $minutes = (int) $date->format('i');
    $seconds = (int) $date->format('s');

    if ($minutes !== 0 || $seconds !== 0) {
      $form_state->setError(
        $error_element,
        $this->t('Scheduled transitions must occur at the top of the hour (minutes and seconds must be 00:00).')
      );
    }
  }

}
