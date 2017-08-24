<?php
/**
 * @file
 * Enables modules and site configuration for a standard site installation.
 *
 * The profilename.profile file has access to almost everything a normal Drupal
 * modulename.module file does because Drupal is fully bootstrapped before almost anything
 * in the profile runs.
 */
use Drupal\Core\Form\FormStateInterface;
use Drupal\utexas\Form\ExtensionSelectForm;

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
    'utexas_install_extensions' => array(
      'display_name' => t('Install extensions'),
      'display' => TRUE,
      'type' => 'batch',
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
 * Install task callback; prepares a batch job to install UTexas extensions.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   The batch job definition.
 */
function utexas_install_extensions(array &$install_state) {
  $batch = array();
  foreach ($install_state['utexas']['modules'] as $module) {
    $batch['operations'][] = ['utexas_install_module', (array) $module];
  }
  return $batch;
}

/**
 * Batch API callback. Installs a module.
 *
 * @param string|array $module
 *   The name(s) of the module(s) to install.
 */
function utexas_install_module($module) {
  \Drupal::service('module_installer')->install((array) $module);
}

/**
 * Redirects the user to a particular URL after installation.
 *
 * @param array $install_state
 *   The current install state.
 *
 * @return array
 *   A renderable array with a success message and a redirect header, if the
 *   extender is configured with one.
 */
function utexas_post_install_redirect(array &$install_state) {
  $redirect = \Drupal::service('utexas.extender')->getRedirect();

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