<?php

/**
 * @file
 * Include.
 */

/**
 * Helper function for creating Flex Content Areas.
 */
function _utexas_devel_create_flex_content_area($media_id) {
  $blockEntityManager = \Drupal::service('entity.manager')
    ->getStorage('block_content');
  $block = $blockEntityManager->create([
    'type' => 'utexas_flex_content_area',
    'reusable' => FALSE,
  ]);
  $block->info = 'Flex Content Area';
  $block->uuid = md5('fca_1' . time());
  $block->field_block_fca[] = [
    'headline' => "Flex Content Area 1",
    'copy_value' => "The Flex Content Area has a number of display options.",
    'link_uri' => "https://utexas.edu",
    'link_text' => "Visit UTexas",
    'image' => [$media_id],
  ];
  $block->field_block_fca[] = [
    'headline' => "Flex Content Area 2",
    'copy_value' => "Flex Content Areas may list links, or a call to action.",
    'link_uri' => "https://utexas.edu",
    'link_text' => "Hook 'em",
    'image' => [$media_id],
  ];
  $block->save();
  $configuration1 = [
    'id' => 'inline_block:utexas_flex_content_area',
    'label' => 'Flex_Content_Area',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_flex_content_area',
  ];

  $block2 = $blockEntityManager->create([
    'type' => 'utexas_flex_content_area',
    'reusable' => FALSE,
  ]);
  $block2->info = 'Flex Content Area';
  $block2->uuid = md5('fca_2' . time());
  $block2->field_block_fca[] = [
    'headline' => "Flex Content Area 3",
    'copy_value' => "They can display in 1, 2, 3, or 4 columns.",
    'link_uri' => "https://utexas.edu",
    'link_text' => "Gone to Texas",
    'image' => [$media_id],
  ];
  $block2->field_block_fca[] = [
    'headline' => "Flex Content Area 4",
    'copy_value' => "Internal and external links can be used.",
    'link_uri' => "https://news.utexas.edu",
    'link_text' => "UTexas News",
    'image' => [$media_id],
  ];
  $block2->save();
  $configuration2 = [
    'id' => 'inline_block:utexas_flex_content_area',
    'label' => 'Flex_Content_Area',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block2->getRevisionId(),
    'view_mode' => 'utexas_flex_content_area',
  ];
  return [
    'block1' => $configuration1,
    'block2' => $configuration2,
  ];
}
