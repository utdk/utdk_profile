<?php

/**
 * @file
 * Drush commands for the UTexas Beacon module.
 */

/**
 * Implements hook_drush_commands().
 */
function utexas_beacon_drush_command() {
  $items = [];
  $items['beacon-info'] = [
    'description' => "Get site information digest",
    'examples' => [
      'drush beacon-info',
    ],
    'aliases' => ['utb'],
  ];
  return $items;
}

/**
 * Implements the drush command.
 */
function drush_utexas_beacon_beacon_info() {
  $data = _utexas_beacon_prepare_data();
  print_r($data);
}
