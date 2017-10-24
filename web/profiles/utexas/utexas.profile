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
  return array(
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
    ]
  );
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
  // Each of the modules set in previous step will be queued up in batch to be enabled.
  foreach ($modules_to_install as $module) {
    $operations[] = [
      'utexas_enable_module', [$module],
    ];
  }

  // Add theme installation options to batch.
  $operations[] = ['utexas_install_theme', []];

  $batch = [
    'title' => t('Adding UTexas flavors...'),
    'operations' => $operations,
    'error_message' => t('The installation has encountered an error.'),
  ];
  return $batch;
}

function utexas_enable_module($module) {
  \Drupal::service('module_installer')->install([$module], TRUE);
}

function utexas_install_theme() {
  // Default to Bartik.
  \Drupal::service('theme_installer')->install(['bartik'], TRUE);
  \Drupal::configFactory()
    ->getEditable('system.theme')
    ->set('default', 'bartik')
    ->save();
}

/**
 * Implements hook_install_tasks_alter().
 */
function utexas_install_tasks_alter(array &$tasks, array $install_state) {
}