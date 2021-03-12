<?php

/**
 * @file
 * Enables modules and site configuration for a standard UTDK installation.
 */

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\utexas\Form\InstallationOptions;
use Drupal\utexas\Form\InstallationComplete;
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
    _utexas_install_footer_content();
    // Function call to create header demo content.
    _utexas_install_header_content();
    $implementations = \Drupal::moduleHandler()->getImplementations('utexas_demo_content');
    $operations = [];
    // Each of the modules with 'utexas_demo_content' implementations
    // will be  added as a batch job.
    foreach ($implementations as $module) {
      $operations[] = [$module . '_utexas_demo_content', []];
    }
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
    'utexas_role_site_manager',
    'utexas_role_content_editor',
  ];
  // Install modules.
  \Drupal::service('module_installer')->install($modules);

  // Add standard permissions to "utexas_site_manager" & "utexas_content_editor"
  // if those roles exist.
  Permissions::assignPermissions('editor', 'utexas_content_editor');
  Permissions::assignPermissions('manager', 'utexas_site_manager');
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
  $user_1_name = 'site-admin';
  // Set default admin account name to site-admin for UI-based installs.
  $form['admin_account']['account']['name']['#default_value'] = $user_1_name;
}

/**
 * Implements hook_page_attachments().
 */
function utexas_page_attachments(array &$attachments) {
  // Add details fieldset optimizations to all pages.
  $attachments['#attached']['library'][] = 'utexas/details-fieldset';
}

/**
 * Implements hook_library_info_alter().
 *
 * Fixes problematic CKEditor save behavior described in
 * https://www.drupal.org/project/drupal/issues/3095304#comment-13983933 .
 * Once this is resolved in Drupal, this hook should be removed.
 * We are using a hook here because "libraries-extend:" is only available in the
 * theme.info.yml file.
 */
function utexas_library_info_alter(&$libraries, $extension) {
  // If it's not the target library, bail.
  if ($extension != 'core' || !isset($libraries['ckeditor'])) {
    return;
  }
  // Get site path of this profile.
  /** @var Drupal\Core\Extension\ExtensionList $extension_list_service */
  $extension_list_service = \Drupal::service('extension.list.profile');
  $profile_path = $extension_list_service->getPath('utexas');
  // Create path to add-on .js file.
  $new_js = '/' . $profile_path . '/js/ckeditorSaveChanges-drupal-3095304.js';
  // Add new_js file to existing array of js files in the library. Preventing
  // aggregation here is probably not necessary, but since the .js file in the
  // target libarary uses "preprocess = false", we do the same.
  $libraries['ckeditor']['js'][$new_js] = ['preprocess' => FALSE];
}

/**
 * Populate footer regions with demo content.
 */
function _utexas_install_footer_content() {
  // Add footer menu links.
  for ($i = 1; $i < 6; $i++) {
    $link = MenuLinkContent::create([
      'title'      => 'Footer Link ' . $i,
      'link'       => ['uri' => 'internal:/'],
      'menu_name'  => 'footer',
      'weight'     => $i,
    ]);
    $link->save();
  }

  // Create block with textarea in Left Footer region.
  $block = BlockContent::create([
    'info' => 'UTexas Block Footer Textarea',
    'type' => 'basic',
    'langcode' => 'en',
    'body' => [
      'value' => '<p class="footer-textarea">Powered by UT Drupal Kit</p>',
      'format' => 'flex_html',
    ],
  ]);
  $block->save();

  $config = \Drupal::config('system.theme');
  $placed_block = Block::create([
    'id' => $block->id(),
    'weight' => 0,
    'theme' => $config->get('default'),
    'status' => TRUE,
    'region' => 'footer_left',
    'plugin' => 'block_content:' . $block->uuid(),
    'settings' => [],
  ]);
  $placed_block->save();

  // Create CTA block and place in Right Footer region.
  $block = BlockContent::create([
    'info' => 'Footer Call to Action',
    'type' => 'call_to_action',
    'langcode' => 'en',
    'field_utexas_call_to_action_link' => [
      'uri' => 'https://utexas.edu',
      'title' => 'Call to Action',
    ],
  ]);
  $block->save();

  $config = \Drupal::config('system.theme');
  $placed_block = Block::create([
    'id' => 'footer_cta_block',
    'weight' => 0,
    'theme' => $config->get('default'),
    'status' => TRUE,
    'region' => 'footer_right',
    'plugin' => 'block_content:' . $block->uuid(),
    'settings' => [
      'label' => 'Footer Call to Action',
      'provider' => 'block_content',
      'label_display' => '0',
      'status' => TRUE,
      'info' => '',
      'view_mode' => 'full',
    ],
  ]);
  $placed_block->save();
}

/**
 * Populate header regions with demo content.
 */
function _utexas_install_header_content() {
  // Populate header menu links.
  for ($i = 1; $i < 4; $i++) {
    $link = MenuLinkContent::create([
      'title'      => 'Header Link ' . $i,
      'link'       => ['uri' => 'internal:/'],
      'menu_name'  => 'header',
      'weight'     => $i,
    ]);
    $link->save();
  }

  // Populate main menu links.
  $menu_link_titles = [
    'Undergraduate Program' => 'internal:/##',
    'Graduate Program' => 'internal:/',
    'Course Directory' => 'internal:/',
    'News' => 'internal:/',
    'Events' => 'internal:/',
    'About' => 'internal:/',
  ];
  $i = 0;
  foreach ($menu_link_titles as $menu_link_title => $uri) {
    $link = MenuLinkContent::create([
      'title'      => $menu_link_title,
      'link'       => ['uri' => $uri],
      'menu_name'  => 'main',
      'weight'     => $i,
      'expanded'   => TRUE,
    ]);
    $link->save();
    $active_link = $link;
    for ($j = 0; $j < 4; $j++) {
      $mid = $active_link->getPluginId();
      $link = MenuLinkContent::create([
        'title'      => 'Lorem Ipsum',
        'link'       => ['uri' => 'internal:/'],
        'menu_name'  => 'main',
        'weight'     => 2,
        'parent'     => $mid,
      ]);
      $link->save();
    }
    $i++;
  }
}

/**
 * Implements template_preprocess_form_element().
 */
function utexas_preprocess_form_element(&$variables) {
  $field_types_to_affect = [
    'checkbox',
    'email',
    'entity_autocomplete',
    'link',
    'managed_file',
    'number',
    'radio',
    'select',
    'textarea',
    'textfield',
  ];
  if (in_array($variables['element']['#type'], $field_types_to_affect)) {
    // Position Form API field descriptions directly below their field labels.
    // Note: we should consider removing & replacing this if and when
    // https://www.drupal.org/node/2318757 becomes available.
    $variables['description_display'] = 'before';
  }
  // Known fields that are not compatible with the `description_display`
  // setting: date, link, item, Media Library, textarea with text format.
}
