<?php

/**
 * @file
 * Enables modules and site configuration for a standard UTDK installation.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block_content\BlockContentInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\utexas\Form\InstallationComplete;
use Drupal\utexas\Form\InstallationOptions;
use Drupal\utexas\Permissions;
use Drupal\utexas\ThemeHelper;
use Drupal\utexas\ToolbarHandler;
use Drupal\utexas\RenderHelper;

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
 * Implements hook_themes_installed().
 */
function utexas_themes_installed($theme_list) {
  // The theme 'Speedway' is being installed.
  if (in_array('speedway', $theme_list)) {

    $config = \Drupal::config('system.theme');
    $default_theme = $config->get('default');
    $theme_config = \Drupal::config($default_theme . '.settings');

    // Load additional theme settings.
    $link = $theme_config->get('parent_link');
    $title = $theme_config->get('parent_link_title');
    $logo_height = $theme_config->get('logo_height');
    $header_secondary_display = $theme_config->get('header_secondary_display');
    $main_menu_alignment = $theme_config->get('main_menu_alignment');

    // Map theme settings to Speedway.
    \Drupal::logger('utexas')->notice('Mapping your theme settings to Speedway...');
    $speedway = \Drupal::configFactory()->getEditable('speedway.settings');
    // Migrate the custom logo, if defined.
    $logo_use_default = $theme_config->get('logo.use_default');
    $logo_path = $theme_config->get('logo.path');
    if ($logo_use_default == FALSE) {
      $speedway->set('logo.use_default', $logo_use_default);
      $speedway->set('logo.path', $logo_path);
    }
    // Save additional theme settings.
    if (isset($link) && isset($title)) {
      $speedway->set('parent_link', $link);
      $speedway->set('parent_link_title', $title);
      $speedway->save();
    }
    if (isset($logo_height)) {
      $speedway->set('logo_height', $logo_height);
      $speedway->save();
    }
    if (isset($header_secondary_display)) {
      $speedway->set('header_secondary_display', $header_secondary_display);
      $speedway->save();
    }
    if (isset($main_menu_alignment)) {
      $speedway->set('main_menu_alignment', $main_menu_alignment);
      $speedway->save();
    }
    // Delete required links block.
    $blocks = \Drupal::entityTypeManager()->getStorage('block')
      ->loadByProperties(['plugin' => 'required_links_block', 'theme' => 'speedway']);
    foreach ($blocks as $block) {
      $block->delete();
    }
    \Drupal::configFactory()->getEditable('block.block.required_links_block')->delete();
  }
}

/**
 * Implements hook_theme_registry_alter().
 */
function utexas_theme_registry_alter(&$theme_registry) {
  $utexas = \Drupal::service('extension.list.profile')->getPath('utexas');
  $theme_registry['feed_block_rss_item']['path'] = $utexas . '/templates';
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
 * Implements hook_form_FORM_ID_alter().
 */
function utexas_form_search_form_alter(&$form, $form_state, $form_id) {
  // Put search tips into a collapsible fieldset (#1686).
  if (!\Drupal::moduleHandler()->moduleExists('search')) {
    return;
  }
  $search_page_repository = \Drupal::service('search.search_page_repository');
  $default_search_page = $search_page_repository->getDefaultSearchPage();
  if (!$default_search_page) {
    return;
  }
  $search_entity = \Drupal::entityTypeManager()->getStorage('search_page')->load($default_search_page);
  $markup = $search_entity->getPlugin()->getHelp();
  // Put search tips into a collapsible fieldset (#1686).
  $form['help_link'] = [
    '#title' => 'About searching',
    '#type' => 'details',
    '#collapsed' => TRUE,
  ];
  $form['help_link']['markup'] = $markup;
}

/**
 * Implements hook_form_alter().
 */
function utexas_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if ($form_id === 'search_block_form') {
    $form['#attributes']['class'][] = 'ut-search-form';
  }
  if ($form_id === 'google_tag_container_form' && $form_state->getFormObject()->getEntity()->isNew()) {
    $form['conditions']['request_path']['pages']['#default_value'] = "/admin*\n/batch*\n/node/add*\n/node/*/edit\n/node/*/delete\n/node/*/layout\n/taxonomy/term/*/edit\n/taxonomy/term/*/layout\n/user/*/edit*\n/user/*/cancel*\n/user/*/layout\n/layout_builder/*";
    $form['conditions']['request_path']['negate']['#default_value'] = TRUE;
    $form['conditions']['response_code']['response_codes']['#default_value'] = "403\n404";
    $form['conditions']['response_code']['negate']['#default_value'] = TRUE;
  }
}

/**
 * Implements hook_page_attachments().
 */
function utexas_page_attachments(array &$attachments) {
  /** @var \Drupal\Core\Routing\CurrentRouteMatch $current_route_match */
  $current_route_match = \Drupal::routeMatch();
  $route_name = $current_route_match->getRouteName();
  // Add details fieldset optimizations to all pages.
  $attachments['#attached']['library'][] = 'utexas/menus';
  if (!\Drupal::service('router.admin_context')->isAdminRoute()) {
    $attachments['#attached']['library'][] = 'utexas/auto-anchors';
    // The utexas-provided "Bootstrap" library includes functionality for
    // Bootstrap alert, collapse, tooltips, modals, navs, and tabs.
    // By default, these libraries will be loaded on all non-administrative
    // pages regardless of the active theme. Sites that need to disable these
    // libraries due to conflicts/incompatibility can do so by setting
    // the `utexas_bootstrap_disable` state to `TRUE` (e.g.,
    // `drush state:set utexas_bootstrap_disable TRUE`).
    // See https://www.drupal.org/docs/develop/drupal-apis/state-api/state-api-overview
    if (\Drupal::state()->get('utexas_bootstrap_disable') !== TRUE) {
      $attachments['#attached']['library'][] = 'utexas/bootstrap5-css';
      $layout_builder_routes = [
        'layout_builder.defaults.node.view',
        'layout_builder.overrides.node.view',
      ];
      if (!in_array($route_name, $layout_builder_routes)) {
        $attachments['#attached']['library'][] = 'utexas/bootstrap5-js';
      }
    }
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

/**
 * Implements hook_preprocess_html().
 */
function utexas_preprocess_html(&$variables) {
  $variables['page']['#attached']['html_head'][] = [
    [
      '#tag' => 'meta',
      '#attributes' => [
        'name' => 'utexas-utdk-version',
        'content' => '3',
      ],
    ],
    'utexas-utdk-version',
  ];
}

/**
 * Implements hook_preprocess_page().
 */
function utexas_preprocess_page(&$variables) {
  // If the current page uses Layout Builder, add a flag.
  if (ThemeHelper::isLayoutBuilderPage()) {
    $variables['is_layout_builder_page'] = TRUE;
  }
  // Year for use in footer copyright.
  $variables['year'] = date('Y');
  /** @var \Drupal\Core\Routing\CurrentRouteMatch $current_route_match */
  $current_route_match = \Drupal::routeMatch();
  $route_name = $current_route_match->getRouteName();
  if ($route_name === 'search.view_google_cse_search') {
    // Remove breadcrumbs block from breadcrumb region.
    unset($variables['page']['breadcrumb']['breadcrumbs']);
  }
}

/**
 * Implements hook_preprocess_block().
 */
function utexas_preprocess_block(&$variables) {
  $base_plugin_id = $variables['base_plugin_id'];
  $content = $variables['elements']['content'] ?? [];
  if (in_array($base_plugin_id, ['menu_block', 'system_menu_block'])) {
    if (isset($variables['elements']['#id'])) {
      $variables['content']['#attributes']['menu-block-id'] = $variables['elements']['#id'];
    }
  }
  if (isset($content['#block_content']) && $content['#block_content'] instanceof BlockContentInterface) {
    // Add bundle identifier.
    $variables['attributes']['class'][] = Html::cleanCssIdentifier('block-bundle-' . $content['#block_content']->bundle());
    // Add legacy identifier to our implementation of contrib feed_block module.
    if ($content['#block_content']->bundle() === 'feed_block') {
      $variables['attributes']['class'][] = 'ut-newsreel';
    }
  }

  if (in_array($base_plugin_id, ['menu_block', 'addtoany_block', 'addtoany_follow_block'])) {
    // AddToAny and Menu block titles should use the smaller `ut-headline`.
    $variables['title_attributes']['class'][] = 'ut-headline';
  }
  else {
    // All other block titles should use `ut-headline--xl`.
    $variables['title_attributes']['class'][] = 'ut-headline--xl';
  }
}

/**
 * Implements hook_user_format_name_alter().
 */
function utexas_user_format_name_alter(&$name, AccountInterface $account) {
  $uid = $account->id();
  // Don't alter anonymous users or objects that do not have any user ID.
  if (empty($uid)) {
    return;
  }
  $user = User::load($uid);
  if ($user && $user->hasField('field_utexas_full_name')) {
    if ($value = ($user->get('field_utexas_full_name')->getString())) {
      // Only if the real name is a non-empty string is $name actually altered.
      if (mb_strlen($value)) {
        $name = $value;
      }
    }
    return;
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function utexas_theme_suggestions_page_alter(array &$suggestions, array $variables) {
  // Add content type suggestions.
  if ($node = \Drupal::request()->attributes->get('node')) {
    if ($node instanceof NodeInterface) {
      array_splice($suggestions, 1, 0, 'page__node__' . $node->getType());
    }
    else {
      $node_revision = \Drupal::entityTypeManager()->getStorage('node')->load($node);
      if ($node_revision instanceof NodeInterface) {
        array_splice($suggestions, 1, 0, 'page__node__' . $node_revision->getType());
      }
    }
  }
}

/**
 * Implements hook_preprocess_field().
 */
function utexas_preprocess_field(&$variables, $hook) {
  if (!isset($variables['element']['#bundle'])) {
    return;
  }
  switch ($variables['element']['#bundle']) {
    case 'feed_block':
      if ($variables['element']['#field_name'] === 'field_read_more') {
        // Add 'button' class to Read more <a> tag.
        $variables['attributes']['class'][] = 'ut-cta';
        $variables['items'][0]['content']['#options']['attributes']['class'][] = 'ut-btn--secondary';
      }
      if ($variables['element']['#field_name'] === 'field_intro_text') {
        $variables['attributes']['class'][] = 'ut-copy';
      }
      break;

    case 'basic':
      if ($variables['element']['#field_name'] === 'body' && $variables['element']['#entity_type'] === 'block_content') {
        $variables['attributes']['class'][] = 'ut-copy';
      }
      break;
  }
}

/**
 * Implements hook_preprocess_breadcrumb().
 */
function utexas_preprocess_breadcrumb(&$variables) {
  // Use a placeholder to inject dynamic content.
  $placeholder_title = [
    '#lazy_builder' => [
      RenderHelper::class . '::lazyBuilder',
      ['page_title'],
    ],
    '#create_placeholder' => TRUE,
  ];
  $variables['breadcrumb'][] = [
    'text' => $placeholder_title,
  ];
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function utexas_theme_suggestions_menu_alter(array &$suggestions, array $variables) {
  if (isset($variables['attributes']['menu-block-id'])) {
    if ($block = Block::load($variables['attributes']['menu-block-id'])) {
      $region = $block->getRegion();
      $suggestions[] = 'menu__' . $region;
    }
  }
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function utexas_theme_suggestions_block_alter(array &$suggestions, array $variables) {
  // Remove the block and replace dashes with underscores in the block ID to
  // use for the hook name.
  $base_plugin_id = $variables['elements']['#base_plugin_id'];
  if (isset($base_plugin_id) && in_array($base_plugin_id, ['system_menu_block', 'menu_block'])) {
    if (isset($variables['elements']['#id'])) {
      $hook = $variables['elements']['#id'];
      $block = Block::load($hook);
      $region = $block->getRegion();
      $suggestions[] = 'block__system_menu_block__' . $region;
    }
  }
}

/**
 * Implements hook_template_preprocess_views_view_table().
 */
function utexas_preprocess_views_view_table(&$variables) {
  // Override this with a sub-theme preprocess hook that removes the class.
  $variables['attributes']['class'][] = 'border-1';
}

/**
 * Implements hook_preprocess_block_system_messages_block().
 */
function utexas_preprocess_block__system_messages_block(&$variables) {
  $variables['content']['#include_fallback'] = FALSE;
}

/**
 * Implements hook_preprocess_status_messages().
 */
function utexas_preprocess_status_messages(&$variables) {
  $variables['#attached']['library'][] = 'utexas/status-messages';
  $variables['attributes']['class'][] = 'status-messages';
}

/**
 * Implements hook_toolbar().
 */
function utexas_toolbar() {
  return \Drupal::service('class_resolver')
    ->getInstanceFromDefinition(ToolbarHandler::class)
    ->toolbar();
}
