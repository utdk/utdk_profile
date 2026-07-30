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
    'utexas_install_features' => [
      'display' => FALSE,
      'type' => 'batch',
      'run' => INSTALL_TASK_RUN_IF_NOT_COMPLETED,
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
 * Batch install News/Event/Profile modules selected during installation.
 */
function utexas_install_features(&$install_state) {
  $state = \Drupal::state();
  if ($state->get('utexas_installation_options.install_news', FALSE)) {
    // We allow non-dependency injection calls.
    // phpcs:ignore
    \Drupal::service('module_installer')->install([
      'utnews',
      'utnews_content_type_news',
      'utnews_block_type_news_listing',
      'utnews_readonly',
      'utnews_view_listing_page',
      'utnews_vocabulary_authors',
      'utnews_vocabulary_categories',
      'utnews_vocabulary_tags',
      'utnews_overrides',
    ]);
  }
  if ($state->get('utexas_installation_options.install_event', FALSE)) {
    // We allow non-dependency injection calls.
    // phpcs:ignore
    \Drupal::service('module_installer')->install([
      'utevent',
      'utevent_content_type_event',
      'utevent_block_type_event_listing',
      'utevent_readonly',
      'utevent_view_listing_page',
      'utevent_vocabulary_location',
      'utevent_vocabulary_tags',
      'utevent_overrides',
    ]);
  }
  if ($state->get('utexas_installation_options.install_profile', FALSE)) {
    // We allow non-dependency injection calls.
    // phpcs:ignore
    \Drupal::service('module_installer')->install([
      'utprof',
      'utprof_content_type_profile',
      'utprof_block_type_profile_listing',
      'utprof_readonly',
      'utprof_view_profiles',
      'utprof_vocabulary_groups',
      'utprof_vocabulary_tags',
      'utprof_overrides',
    ]);
  }
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
    InstallationHelper::installFooterContent();
    InstallationHelper::installHeaderContent();
    InstallationHelper::installSocialLinks();
    $state = \Drupal::state();
    if ($state->get('utexas_installation_options.install_news', FALSE)) {
      // We allow non-dependency injection calls.
      // phpcs:ignore
      \Drupal::service('module_installer')->install(['utnews_demo_content']);
    }
    if ($state->get('utexas_installation_options.install_event', FALSE)) {
      // We allow non-dependency injection calls.
      // phpcs:ignore
      \Drupal::service('module_installer')->install(['utevent_demo_content']);
    }
    if ($state->get('utexas_installation_options.install_profile', FALSE)) {
      // We allow non-dependency injection calls.
      // phpcs:ignore
      \Drupal::service('module_installer')->install(['utprof_demo_content']);
    }
  }
}

/**
 * Perform final cleanup tasks.
 */
function utexas_install_cleanup(&$install_state) {
  // Set default country and timezone after form completion.
  \Drupal::configFactory()
    ->getEditable('system.date')
    ->set('timezone.default', 'America/Chicago')
    ->set('country.default', 'US')->save();
}

/**
 * Perform final module installation task.
 */
function utexas_install_post_installation_modules(&$install_state) {
  // Add modules that depend on installation configuration.
  $modules = [
    'utexas_role_content_editor',
    'utexas_role_site_manager',
  ];
  // Install modules.
  \Drupal::service('module_installer')->install($modules);

  // Add editing permissions to "utexas_content_editor".
  Permissions::assignPermissions('editor', 'utexas_content_editor');
  // Add management permissions to "utexas_site_manager".
  Permissions::assignPermissions('manager', 'utexas_site_manager');
}

/**
 * Implements hook_install_tasks_alter().
 */
function utexas_install_tasks_alter(array &$tasks, array $install_state) {
  unset($tasks['install_select_language']);
}
