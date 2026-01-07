<?php

/**
 * @file
 * Enables modules and site configuration for a standard UTDK installation.
 */

use Drupal\utexas\Form\InstallationComplete;
use Drupal\utexas\Form\InstallationOptions;
use Drupal\utexas\InstallationHelper;
use Drupal\utexas\Permissions;

/**
 * Implements hook_install_tasks().
 */
function utexas_install_tasks() {
  return [
    'utexas_installation_options' => [
      'display_name' => t('Installation options'),
      'display' => TRUE,
      'type' => 'form',
      'function' => InstallationOptions::class,
    ],
    'utexas_install_demo_content' => [
      'display' => FALSE,
      'type' => 'batch',
      'run' => INSTALL_TASK_RUN_IF_NOT_COMPLETED,
    ],
    'utexas_install_cleanup' => [
      'display' => FALSE,
      'type' => 'batch',
      'run' => INSTALL_TASK_RUN_IF_NOT_COMPLETED,
    ],
    'utexas_install_post_installation_modules' => [
      'display' => FALSE,
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
 * Batch installation of demo content.
 *
 * This installs specific demo content, then invokes any implementations of
 * hook_utexas_demo_content().
 */
function utexas_install_demo_content(&$install_state) {
  // Note: the equivalent can be achieved during a drush site installation:
  // drush si utexas utexas_installation_options.default_content=NULL -y .
  $create_default_content = \Drupal::state()->get('utexas_installation_options.default_content', FALSE);
  if ($create_default_content) {
    // Function call to create footer demo content.
    InstallationHelper::installFooterContent();
    // Function call to create header demo content.
    InstallationHelper::installHeaderContent();

    // Each of the 'utexas_demo_content' implementations will be added as a
    // batch job.
    \Drupal::moduleHandler()->invokeAllWith(
      'utexas_demo_content',
      function (callable $hook, string $module) use (&$operations) {
        $operations[] = [$module . '_utexas_demo_content', []];
      }
    );

    $batch = [
      'title' => t('Generating demo content...'),
      'operations' => $operations,
      'error_message' => t('Demo content generation has encountered an error.'),
    ];
    return $batch;
  }
}

/**
 * Perform final cleanup tasks.
 */
function utexas_install_cleanup(&$install_state) {
  // Remove default search entities.
  $search_storage = \Drupal::entityTypeManager()->getStorage('search_page');
  $entities = $search_storage->loadMultiple(['node_search', 'user_search']);
  $search_storage->delete($entities);
  // Set default country and timezone after form completion.
  \Drupal::configFactory()
    ->getEditable('system.date')
    ->set('timezone.default', 'America/Chicago')
    ->set('country.default', 'US')
    ->save(TRUE);

  // Set base value for max-age in Cache-Control header for reverse proxies.
  $config = \Drupal::service('config.factory')->getEditable('system.performance');
  $config->set('cache.page.max_age', 900);
  $config->save();
}

/**
 * Perform final module installation task.
 */
function utexas_install_post_installation_modules(&$install_state) {
  // Add modules that depend on installation configuration.
  $modules = [
    'utexas_role_content_editor',
  ];
  // Install modules.
  \Drupal::service('module_installer')->install($modules);

  // Add editing permissions to "utexas_content_editor".
  Permissions::assignPermissions('editor', 'utexas_content_editor');
}

/**
 * Implements hook_install_tasks_alter().
 */
function utexas_install_tasks_alter(array &$tasks, array $install_state) {
  unset($tasks['install_select_language']);
}
