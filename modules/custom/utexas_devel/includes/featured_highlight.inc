<?php

/**
 * @file
 * Include.
 */

/**
 * Helper function for creating Featured Highlights.
 */
function _utexas_devel_create_featured_highlight($media_id) {
  $blockEntityManager = \Drupal::entityTypeManager()
    ->getStorage('block_content');
  $block = $blockEntityManager->create([
    'type' => 'utexas_featured_highlight',
    'reusable' => FALSE,
  ]);
  $block->info = 'Featured Highlight';
  $block->uuid = md5('featured_highlight_1' . time());
  $block->field_block_featured_highlight = [
    'headline' => "Featured Highlight",
    'media' => [$media_id],
    'date' => "2019-06-12",
    "copy_value" => "Add descriptive text to provide a short summary of this featured content.",
    "link_uri" => "https://www.utexas.edu",
    "link_text" => "Visit UTexas",
  ];
  $block->save();
  $configuration1 = [
    'id' => 'inline_block:utexas_featured_highlight',
    'label' => 'Featured Highlight',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_featured_highlight_3',
  ];

  $block = $blockEntityManager->create([
    'type' => 'utexas_featured_highlight',
    'reusable' => FALSE,
  ]);
  $block->info = 'Featured Highlight';
  $block->uuid = md5('featured_highlight_2' . time());
  $block->field_block_featured_highlight = [
    'headline' => "Timely Content",
    'media' => [$media_id],
    'date' => "2020-01-31",
    "copy_value" => "Featured Highlights have a call to action & date field.",
    "link_uri" => "https://www.utexas.edu",
    "link_text" => "Gone to Texas",
  ];
  $block->save();
  $configuration2 = [
    'id' => 'inline_block:utexas_featured_highlight',
    'label' => 'Featured Highlight',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_featured_highlight_2',
  ];
  return [
    'block1' => $configuration1,
    'block2' => $configuration2,
  ];
}
