<?php

/**
 * @file
 * Include.
 */

/**
 * Helper function for creating Quick Links.
 */
function _utexas_devel_create_quick_links() {
  $blockEntityManager = \Drupal::service('entity.manager')
    ->getStorage('block_content');
  $block = $blockEntityManager->create([
    'type' => 'utexas_quick_links',
    'reusable' => FALSE,
  ]);
  $block->info = 'Quick Links';
  $block->uuid = md5('ql_1' . time());
  $block->field_block_ql = [
    'headline' => "Quick Links",
    'copy_value' => "Quick links include a headline, copy text, and links.",
    'links' => serialize([
      "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
      "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
      "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
    ]),
  ];
  $block->save();
  $configuration1 = [
    'id' => 'inline_block:utexas_quick_links',
    'label' => 'Quick Links',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_quick_links',
  ];

  $block = $blockEntityManager->create([
    'type' => 'utexas_quick_links',
    'reusable' => FALSE,
  ]);
  $block->info = 'Quick Links';
  $block->uuid = md5('ql_2' . time());
  $block->field_block_ql = [
    'headline' => "Quick Links",
    'copy_value' => "Quick Links include a headline, copy text, and links.",
    'links' => serialize([
      "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
      "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
      "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
    ]),
  ];
  $block->save();
  $configuration2 = [
    'id' => 'inline_block:utexas_quick_links',
    'label' => 'Quick Links',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_quick_links',
  ];
  return [
    'block1' => $configuration1,
    'block2' => $configuration2,
  ];
}
