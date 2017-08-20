<?php

namespace Drupal\utexas_installer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class UTexasInstallerFormSubmit
 * @package Drupal\utexas_installer
 *
 * This submit function is attached to the 'install-configure-form'
 * so that we can have influence on the installation process,
 * and handle drush installations properly.
 */
class UTexasInstallerFormSubmit {
  public function doSubmit(&$form, FormStateInterface &$form_state) {
    // This allows the user to pass 'default-forty-acres' in their
    // drush command. Value must be 'true' to enable theme by default.
    // Note the '--strict=0', which is required to pass this param to
    // drush.
    // E.g.:
    // drush @utdk8-shared.local si utexas -y --default-forty-acres=true --strict=0
    $theme_option_from_drush = drush_get_option("default-forty-acres");
    if ($theme_option_from_drush == "true") {
      $form_state->setValue('install_forty_acres_theme_option', 1);
    }
  }

}