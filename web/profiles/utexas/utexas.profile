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
use Drupal\block\Entity\Block;
use Drupal\block_content\Entity\BlockContent;
use Drupal\menu_link_content\Entity\MenuLinkContent;

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
    'utexas_install_demo_content' => [
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
  $operations[] = ['utexas_install_theme', ['forty_acres']];

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
 * Batch installation of demo content.
 *
 * This simply invokes any implementations of hook_utexas_demo_content().
 * It runs after the general `utexas_install_batch_processing` to
 * ensure that the implementing modules are already installed.
 */
function utexas_install_demo_content(&$install_state) {
  $create_default_content = \Drupal::state()->get('utexas-install.default_content', FALSE);
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
  // Set default admin account name to site-admin for drush-based installs.
  if (PHP_SAPI == 'cli' && function_exists('drush_main')) {
    $account_name = drush_get_option('account-name', FALSE);
    if (!$account_name) {
      $form['admin_account']['account']['name']['#value'] = $user_1_name;
    }
  }
}

/**
 * Implements hook_page_attachments().
 */
function utexas_page_attachments(array &$attachments) {
  // Add details fieldset optimizations to all pages.
  $attachments['#attached']['library'][] = 'utexas/details-fieldset';
}

/**
 * Populate footer regions with demo content.
 */
function _utexas_install_footer_content() {
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
      'menu_name'  => 'header_menu',
      'weight'     => $i,
    ]);
    $link->save();
  }

  // Populate main menu links.
  $menu_link_titles = [
    'Undergraduate Program' => 'route:<nolink>',
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
