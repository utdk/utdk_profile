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

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function utexas_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  $form['install_default_option'] = [
    '#type' => 'checkbox',
    '#title' => 'Install Forty Acres default theme?',
    '#description' => 'Check this option to have the Forty Acres theme installed.'
  ];

  $form['#submit'][] = 'utexas_form_install_configure_submit';
}

/**
 * Submission handler to sync the contact.form.feedback recipient.
 */
function utexas_form_install_configure_submit($form, FormStateInterface $form_state) {
  $enable_forty_acres_theme = $form_state->getValue('install_default_option');
  if ($enable_forty_acres_theme == '1') {
    // Install default theme.
    \Drupal::service('theme_installer')->install(['forty_acres']);
    \Drupal::configFactory()
      ->getEditable('system.theme')
      ->set('default', 'forty_acres')
      ->save();
  }
}
