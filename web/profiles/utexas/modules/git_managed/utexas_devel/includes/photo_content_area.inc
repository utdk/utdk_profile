<?php

/**
 * @file
 * Include.
 */

/**
 * Helper function for creating Featured Highlights.
 */
function _utexas_devel_create_photo_content_area($media_id) {
  $blockEntityManager = \Drupal::service('entity.manager')
    ->getStorage('block_content');
  $block = $blockEntityManager->create([
    'type' => 'utexas_photo_content_area',
    'reusable' => FALSE,
  ]);
  $block->info = 'Photo Content Area';
  $block->uuid = md5('pca_1' . time());
  $block->field_block_pca = [
    'headline' => "Photo Content Area",
    'copy_value' => "Photo content Areas include image, headline, credit, copy text, and links.",
    'links' => serialize([
      "0" => ['url' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
      "1" => ['url' => "https://www.utexas.edu", 'title' => "Meet our staff"],
      "2" => ['url' => "https://www.utexas.edu", 'title' => "Student guide"],
    ]),
    'photo_credit' => "Ⓒ Photo by University of Texas at Austin",
    'image' => [$media_id],
  ];
  $block->save();
  $configuration1 = [
    'id' => 'inline_block:utexas_photo_content_area',
    'label' => 'Photo Content Area',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_photo_content_area',
  ];

  $block = $blockEntityManager->create([
    'type' => 'utexas_photo_content_area',
    'reusable' => FALSE,
  ]);
  $block->info = 'Photo Content Area';
  $block->uuid = md5('pca_2' . time());
  $block->field_block_pca = [
    'headline' => "Photo Content Area",
    'copy_value' => "All fields are optional",
    'links' => serialize([
      "0" => ['url' => "https://www.utexas.edu", 'title' => "Undergraduate applications"],
      "1" => ['url' => "https://www.utexas.edu", 'title' => "Graduate applications"],
      "2" => ['url' => "https://www.utexas.edu", 'title' => "Post-doctoral fellowships"],
    ]),
    'image' => [$media_id],
  ];
  $block->save();
  $configuration2 = [
    'id' => 'inline_block:utexas_photo_content_area',
    'label' => 'Photo Content Area',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_photo_content_area',
  ];
  return [
    'block1' => $configuration1,
    'block2' => $configuration2,
  ];
}
