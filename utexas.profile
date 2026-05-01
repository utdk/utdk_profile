<?php

/**
 * @file
 * Enables modules and site configuration for a standard UTDK installation.
 */

use Drupal\Core\Url;
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
    InstallationHelper::installFooterContent();
    InstallationHelper::installHeaderContent();
    InstallationHelper::installSocialLinks();
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

/**
 * Implements hook_navigation_content_top().
 */
function utexas_navigation_content_top(): array {
  if (!\Drupal::state()->get('display_links')) {
    return [];
  }
  $account = \Drupal::currentUser();
  if (!$account->hasRole('utexas_site_manager') && !$account->hasRole('utexas_content_editor')) {
    return [];
  }
  $host = \Drupal::request()->getHost();
  $support_url = 'mailto:drupal-kit-support@utlists.utexas.edu?subject=Support%20Request%20from%20' . rawurlencode($host);

  return [
    'utexas_support_links' => [
      '#theme' => 'navigation_menu',
      '#items' => [
        'support' => [
          'title' => t('Open Support Ticket'),
          'url' => Url::fromUri($support_url),
        ],
        'docs' => [
          'title' => t('Drupal Kit Documentation'),
          'url' => Url::fromUri('https://drupalkit.its.utexas.edu/docs'),
          'attributes' => ['target' => '_blank'],
        ],
        'demo' => [
          'title' => t('Drupal Kit Demo site'),
          'url' => Url::fromUri('https://demo.drupalkit.its.utexas.edu/'),
          'attributes' => ['target' => '_blank'],
        ],
      ],
      '#cache' => [
        'contexts' => ['user.roles', 'url.site'],
      ],
    ],
  ];
}
