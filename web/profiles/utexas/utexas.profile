<?php

/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 *
 * The profilename.profile file has access to almost everything a normal Drupal
 * modulename.module file does because Drupal is fully bootstrapped before
 * almost anything in the profile runs.
 */

use Drupal\utexas\Form\ExtensionSelectForm;
use Drupal\utexas\Form\InstallationComplete;

/**
 * Implements hook_install_tasks().
 */
function utexas_install_tasks() {
  return [
    'utexas_select_extensions' => [
      'display_name' => t('Flavors of Texas'),
      'display' => TRUE,
      'type' => 'form',
      'function' => ExtensionSelectForm::class,
    ],
    'utexas_install_batch_processing' => [
      'display_name' => t('Apply flavoring'),
      'display' => TRUE,
      'type' => 'batch',
      'run' => INSTALL_TASK_RUN_IF_NOT_COMPLETED,
    ],
    'utexas_finish_installation' => [
      'display_name' => t('Installation complete'),
      'display' => TRUE,
      'type' => 'form',
      'function' => InstallationComplete::class,
    ],
  ];
}

/**
 * Custom installation batch process.
 *
 * This will enable modules and do configuration via batch.
 *
 * This creates an operations array defining what batch should do, including
 * what it should do when it's finished.
 */
function utexas_install_batch_processing(&$install_state) {
  $modules_to_install = \Drupal::state()->get('utexas-install.modules_to_enable', []);
  $operations = [];
  // Each of the modules set in previous step will be enabled.
  foreach ($modules_to_install as $module) {
    $operations[] = [
      'utexas_enable_module', [$module],
    ];
  }

  // Add theme installation options to batch.
  $operations[] = ['utexas_install_theme', ['bartik']];

  // Check if we should install realistic default content.
  // @see ExtensionSelectForm::submitForm().
  $create_default_content = \Drupal::state()->get('utexas-install.default_content', FALSE);
  if ($create_default_content) {
    foreach ($modules_to_install as $module) {
      $operations[] = [
        'utexas_create_default_content', [$module],
      ];
    }
  }

  $batch = [
    'title' => t('Adding UTexas flavors...'),
    'operations' => $operations,
    'error_message' => t('The installation has encountered an error.'),
  ];
  return $batch;
}

/**
 * Helper batch callback to enable a module.
 */
function utexas_enable_module($module) {
  \Drupal::service('module_installer')->install([$module], TRUE);
}

/**
 * Helper batch callback to configure and enable theme.
 */
function utexas_install_theme($theme) {
  // Default to Bartik.
  \Drupal::service('theme_installer')->install([$theme], TRUE);
  \Drupal::configFactory()
    ->getEditable('system.theme')
    ->set('default', $theme)
    ->save();
}

/**
 * Helper function to execute module default content function, if it exists.
 *
 * @param string $module
 *   A module name.
 */
function utexas_create_default_content($module) {
  $moduleHandler = \Drupal::service('module_handler');
  if ($moduleHandler->moduleExists($module)) {
    $function = '_' . $module . '_default_content';
    if (function_exists($function)) {
      call_user_func($function);
    }
  }
}

/**
 * Implements hook_install_tasks_alter().
 */
function utexas_install_tasks_alter(array &$tasks, array $install_state) {
  unset($tasks['install_select_language']);
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function utexas_form_install_configure_form_alter(&$form, $form_state, $form_id) {
  // Unsetting Country and Timezone selects from installation form.
  unset($form['regional_settings']);
}

/**
 * Implements hook_page_attachments().
 */
function utexas_page_attachments(array &$attachments) {
  // Add details fieldset optimizations to all pages.
  $attachments['#attached']['library'][] = 'utexas/details-fieldset';
}
