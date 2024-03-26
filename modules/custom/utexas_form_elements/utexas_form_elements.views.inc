<?php

/**
 * @file
 * Provide views data for our custom module_name.
 */

/**
 * Implements hook_views_data().
 */
function utexas_form_elements_views_data() {

  $data['views']['add_flex_page'] = [
    'title' => t('Link for "Add new Flex Page content"'),
    'help' => t('Display an "Add new Flex Page content" link.'),
    'area' => [
      'id' => 'add_flex_page',
    ],
  ];

  $data['views']['set_site_homepage'] = [
    'title' => t('Link for "Set site homepage"'),
    'help' => t('Display a "Set site homepage" link.'),
    'area' => [
      'id' => 'set_site_homepage',
    ],
  ];

  return $data;
}
