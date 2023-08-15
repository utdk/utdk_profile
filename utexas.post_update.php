<?php

/**
 * @file
 * Post update hooks. Applied after all other updates/install functions.
 */

use Symfony\Component\Yaml\Yaml;

/**
 * Update entity_clone cloneable entities configuration.
 */
function utexas_post_update_8150() {
  $config_name = 'entity_clone.cloneable_entities';
  $config = \Drupal::configFactory()->getEditable($config_name);
  $config_path = \Drupal::service('extension.list.profile')->getPath('utexas') . '/config/install/' . $config_name . '.yml';
  if (!empty($config_path)) {
    $data = Yaml::parse(file_get_contents($config_path));
    if (is_array($data)) {
      $config->setData($data)->save(TRUE);
    }
  }
}
