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
use Drupal\Core\Url;

/**
 * Implements hook_install_tasks().
 */
function utexas_install_tasks() {
  return array(
    'utexas_select_extensions' => array(
      'display_name' => t('Choose UTexas extensions'),
      'display' => TRUE,
      'type' => 'form',
      'function' => ExtensionSelectForm::class,
    ),
  );
}

/**
 * Implements hook_install_tasks_alter().
 */
function utexas_install_tasks_alter(array &$tasks, array $install_state) {
  $tasks['install_finished']['function'] = 'utexas_post_install_redirect';
}

/**
 * Redirects the user to a particular URL after installation.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   A renderable array with a success message and a redirect header.
 */
function utexas_post_install_redirect(array &$install_state) {
  $redirect = get_installer_redirect();

  $output = [
    '#title' => t('Ready to rock'),
    'info' => [
      '#markup' => t('Congratulations, you installed UT Drupal Kit! If you are not redirected in 5 seconds, <a href="@url">click here</a> to proceed to your site.', [
        '@url' => $redirect,
      ]),
    ],
    '#attached' => [
      'http_header' => [
        ['Cache-Control', 'no-cache'],
      ],
    ],
  ];

  // The installer doesn't make it easy (possible?) to return a redirect
  // response, so set a redirection META tag in the output.
  $meta_redirect = [
    '#tag' => 'meta',
    '#attributes' => [
      'http-equiv' => 'refresh',
      'content' => '0;url=' . $redirect,
    ],
  ];
  $output['#attached']['html_head'][] = [$meta_redirect, 'meta_redirect'];

  return $output;

}

/**
 * Helper function to return a redirect object to the homepage.
 */
function get_installer_redirect() {
  $path = '<front>';
  $redirect = Url::fromUri('internal:/' . $path);
  // Explicitly set the base URL, if not previously set, to prevent weird
  // redirection snafus.
  $base_url = $redirect->getOption('base_url');
  if (empty($base_url)) {
    $redirect->setOption('base_url', $GLOBALS['base_url']);
  }
  return $redirect->setOption('absolute', TRUE)->toString();
}
