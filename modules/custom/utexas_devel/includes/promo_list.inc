<?php

/**
 * @file
 * Include.
 */

/**
 * Helper function for creating Promo Lists.
 */
function _utexas_devel_create_promo_list($media_id) {
  $blockEntityManager = \Drupal::entityTypeManager()
    ->getStorage('block_content');
  $block = $blockEntityManager->create([
    'type' => 'utexas_promo_list',
    'reusable' => FALSE,
  ]);
  $block->info = 'Promo List';
  $block->uuid = md5('promo_list_1' . time());
  $block->field_block_pl = [
    'headline' => "Promo List Group 1",
    "promo_list_items" => serialize([
      [
        'item' => [
          'headline' => "Item 1",
          'image' => $media_id,
          'copy' => [
            'value' => "Short descriptive text can be <b>formatted</b>.",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => 'https://www.utexas.edu',
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Item 2",
          'image' => $media_id,
          'copy' => [
            'value' => "Add informational text to describe the item",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => 'https://www.utexas.edu',
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Item 3",
          'image' => $media_id,
          'copy' => [
            'value' => "Promo lists are unlimited",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => 'https://www.utexas.edu',
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Item 4",
          'image' => $media_id,
          'copy' => [
            'value' => "Promo lists can be displayed in various column formats",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => 'https://www.utexas.edu',
          ],
        ],
      ],
    ]),
  ];
  $block->save();
  $configuration1 = [
    'id' => 'inline_block:utexas_promo_list',
    'label' => 'Promo List',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_promo_list_2',
  ];

  $block = $blockEntityManager->create([
    'type' => 'utexas_promo_list',
    'reusable' => FALSE,
  ]);
  $block->info = 'Promo List';
  $block->uuid = md5('promo_list_2' . time());
  $block->field_block_pl = [
    'headline' => "Promo List Group 2",
    "promo_list_items" => serialize([
      [
        'item' => [
          'headline' => "Item 5",
          'image' => $media_id,
          'copy' => [
            'value' => "Promo lists can be displayed in various column formats",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => 'https://www.utexas.edu',
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Item 6",
          'image' => $media_id,
          'copy' => [
            'value' => "Promo lists are unlimited",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => 'https://www.utexas.edu',
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Item 7",
          'image' => $media_id,
          'copy' => [
            'value' => "Add informational text to describe the item",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => 'https://www.utexas.edu',
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Item 8",
          'image' => $media_id,
          'copy' => [
            'value' => "Add informational text to describe the item",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => 'https://www.utexas.edu',
          ],
        ],
      ],
    ]),
  ];
  $block->save();
  $configuration2 = [
    'id' => 'inline_block:utexas_promo_list',
    'label' => 'Promo List',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_promo_list_2',
  ];
  return [
    'block1' => $configuration1,
    'block2' => $configuration2,
  ];
}
