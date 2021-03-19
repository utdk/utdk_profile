<?php

/**
 * @file
 * Include.
 */

/**
 * Helper function for creating Resources.
 */
function _utexas_devel_create_resources($media_id) {
  $blockEntityManager = \Drupal::entityTypeManager()
    ->getStorage('block_content');
  $block = $blockEntityManager->create([
    'type' => 'utexas_resources',
    'reusable' => FALSE,
  ]);
  $block->info = 'Resources';
  $block->uuid = md5('resource_1' . time());
  $block->field_block_resources = [
    'headline' => "Resource Group 1",
    "resource_items" => serialize([
      [
        'item' => [
          'headline' => "Resource 1",
          'image' => $media_id,
          'links' => [
            "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
            "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
            "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Resource 2",
          'image' => $media_id,
          'links' => [
            "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
            "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
            "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
          ],
        ],
      ],
    ]),
  ];
  $block->save();
  $configuration1 = [
    'id' => 'inline_block:utexas_resources',
    'label' => 'Resource',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_resource',
  ];

  $block = $blockEntityManager->create([
    'type' => 'utexas_resources',
    'reusable' => FALSE,
  ]);
  $block->info = 'Resources';
  $block->uuid = md5('resource_2' . time());
  $block->field_block_resources = [
    'headline' => "Resource Group 1",
    "resource_items" => serialize([
      [
        'item' => [
          'headline' => "Resource 1",
          'image' => $media_id,
          'links' => [
            "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
            "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
            "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Resource 2",
          'image' => $media_id,
          'links' => [
            "0" => ['uri' => "https://www.utexas.edu", 'title' => "Our commitment to diversity"],
            "1" => ['uri' => "https://www.utexas.edu", 'title' => "Meet our staff"],
            "2" => ['uri' => "https://www.utexas.edu", 'title' => "Student guide"],
          ],
        ],
      ],
    ]),
  ];
  $block->save();
  $configuration2 = [
    'id' => 'inline_block:utexas_resources',
    'label' => 'Resource',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_resource',
  ];
  return [
    'block1' => $configuration1,
    'block2' => $configuration2,
  ];
}
