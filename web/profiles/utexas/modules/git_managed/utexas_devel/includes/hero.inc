<?php

/**
 * @file
 * Include.
 */

/**
 * Helper function for creating Hero styles.
 */
function _utexas_devel_create_hero($media_id, $view_mode) {
  $blockEntityManager = \Drupal::service('entity.manager')
    ->getStorage('block_content');
  $block = $blockEntityManager->create([
    'type' => 'utexas_hero',
    'reusable' => FALSE,
  ]);
  $block->info = 'Hero';
  $block->uuid = md5($view_mode . '_1' . time());
  $block->field_block_hero = [
    'heading' => "Hero",
    'subheading' => "Subheading content",
    'media' => [$media_id],
    "caption" => "A short caption may be added, describing the hero",
    "credit" => "Copyright University of Texas at Austin",
    "link_uri" => "https://www.utexas.edu",
    "link_title" => "Visit Texas",
  ];
  $block->save();
  $configuration1 = [
    'id' => 'inline_block:utexas_hero',
    'label' => 'Hero',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => $view_mode,
  ];

  $block = $blockEntityManager->create([
    'type' => 'utexas_hero',
    'reusable' => FALSE,
  ]);
  $block->info = 'Hero';
  $block->uuid = md5($view_mode . '_2' . time());
  $block->field_block_hero = [
    'heading' => "New and Improved",
    'subheading' => "Heros can display in 6 styles",
    'media' => [$media_id],
    "link_uri" => "https://www.utexas.edu",
    "link_title" => "Visit Texas",
    "caption" => "A short caption may be added, describing the hero",
    "credit" => "Copyright University of Texas at Austin",
  ];
  $block->save();
  $configuration2 = [
    'id' => 'inline_block:utexas_hero',
    'label' => 'Hero',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => $view_mode,
  ];
  return [
    'block1' => $configuration1,
    'block2' => $configuration2,
  ];
}
