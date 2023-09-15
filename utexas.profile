<?php

/**
 * @file
 * Enables modules and site configuration for a standard UTDK installation.
 */

use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Form\FormStateInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\user\Entity\User;
use Drupal\utexas\Form\InstallationComplete;
use Drupal\utexas\Form\InstallationOptions;
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
 * Implements hook_theme().
 */
function utexas_theme($existing, $type, $theme, $path) {
  // Register templates defined in /templates.
  return [
    'block__addtoany' => [
      'base hook' => 'block',
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
 * Implements hook_form_alter().
 */
function utexas_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if ($form_id === 'google_tag_container_form' && $form_state->getFormObject()->getEntity()->isNew()) {
    $form['conditions']['request_path']['pages']['#default_value'] = "/admin*\n/batch*\n/node/add*\n/node/*/edit\n/node/*/delete\n/node/*/layout\n/taxonomy/term/*/edit\n/taxonomy/term/*/layout\n/user/*/edit*\n/user/*/cancel*\n/user/*/layout";
    $form['conditions']['request_path']['negate']['#default_value'] = TRUE;
    $form['conditions']['response_code']['response_codes']['#default_value'] = "403\n404";
    $form['conditions']['response_code']['negate']['#default_value'] = TRUE;
  }
}

/**
 * Implements hook_page_attachments().
 */
function utexas_page_attachments(array &$attachments) {
  // Add details fieldset optimizations to all pages.
  $attachments['#attached']['library'][] = 'utexas/menus';
  if (!\Drupal::service('router.admin_context')->isAdminRoute()) {
    $attachments['#attached']['library'][] = 'utexas/auto-anchors';
  }
}

/**
 * Populate footer regions with demo content.
 */
function _utexas_install_footer_content() {
  // Add footer menu links.
  for ($i = 1; $i < 6; $i++) {
    $link = MenuLinkContent::create([
      'title'      => 'Footer Link ' . $i,
      'link'       => ['uri' => 'route:<nolink>'],
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
      'link'       => ['uri' => 'route:<nolink>'],
      'menu_name'  => 'header',
      'weight'     => $i,
    ]);
    $link->save();
  }

  // Populate main menu links.
  $menu_link_titles = [
    'Undergraduate Program' => 'route:<nolink>##',
    'Graduate Program' => 'route:<nolink>',
    'Course Directory' => 'route:<nolink>',
    'News' => 'route:<nolink>',
    'Events' => 'route:<nolink>',
    'About' => 'route:<nolink>',
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
        'link'       => ['uri' => 'route:<nolink>'],
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
    'password',
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

/**
 * Implements hook_link_alter().
 */
function utexas_link_alter(&$variables) {
  // Add a targetable class to menu links not visible to anonymous users.
  // This is modeled on conversation at
  // https://www.drupal.org/project/drupal/issues/2665320.
  // Url::access() checks isRouted(), so we do not need to check ourselves.
  if (!($variables['url']->access(User::getAnonymousUser()))) {
    if (isset($variables['options']['attributes']['class']) && !is_array($variables['options']['attributes']['class'])) {
      // Avoid casting to a class as a string, such as in https://git.drupalcode.org/project/redirect/-/blob/8.x-1.x/redirect.module#L375.
      $variables['options']['attributes']['class'] = explode(',', $variables['options']['attributes']['class']);
    }
    // Add the a.access-protected class for CSS styling.
    $variables['options']['attributes']['class'][] = 'access-protected';
    $variables['options']['attributes']['title'] = 'This link is not visible to non-authenticated users.';
  }
}

/**
 * Implements hook_contextual_links_plugins_alter().
 */
function utexas_contextual_links_plugins_alter(array &$contextual_links) {
  // Change Layout Builder "Configure" link to "Edit" (utdk_profile/2094).
  if (isset($contextual_links['layout_builder_block_update'])) {
    $contextual_links['layout_builder_block_update']['title'] = t('Edit');
  }
  // The three Layout Builder contextual links for blocks are
  // Configure, Move, and Remove Block. To make them all consistent, we drop the
  // 'Block' from the remove block item.
  if (isset($contextual_links['layout_builder_block_remove'])) {
    $contextual_links['layout_builder_block_remove']['title'] = t('Remove');
  }
}
