<?php

/**
 * @file
 * Include.
 */

/**
 * Helper function for creating Promo Units.
 */
function _utexas_devel_create_promo_unit($media_id) {
  $blockEntityManager = \Drupal::service('entity.manager')
    ->getStorage('block_content');
  $block = $blockEntityManager->create([
    'type' => 'utexas_promo_unit',
    'reusable' => FALSE,
  ]);
  $block->info = 'Promo Unit';
  $block->uuid = md5('promo_unit_1' . time());
  $block->field_block_pu = [
    'headline' => "Promo Unit Group 1",
    "promo_unit_items" => serialize([
      [
        'item' => [
          'headline' => "Item 1",
          'image' => $media_id,
          'copy' => [
            'value' => "Short descriptive text can be <b>formatted</b>.",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => "https://www.utexas.edu",
            'title' => "Visit Texas",
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
            'uri' => "https://www.utexas.edu",
            'title' => "Visit Texas",
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Item 3",
          'image' => $media_id,
          'copy' => [
            'value' => "Promo units are unlimited",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => "https://www.utexas.edu",
            'title' => "Visit Texas",
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Item 4",
          'image' => $media_id,
          'copy' => [
            'value' => "Promo units can be displayed in various column formats",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => "https://www.utexas.edu",
            'title' => "Visit Texas",
          ],
        ],
      ],
    ]),
  ];
  $block->save();
  $configuration1 = [
    'id' => 'inline_block:utexas_promo_unit',
    'label' => 'Promo Unit',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_promo_unit',
  ];

  $block = $blockEntityManager->create([
    'type' => 'utexas_promo_unit',
    'reusable' => FALSE,
  ]);
  $block->info = 'Promo Unit';
  $block->uuid = md5('promo_unit_2' . time());
  $block->field_block_pu = [
    'headline' => "Promo Unit Group 2",
    "promo_unit_items" => serialize([
      [
        'item' => [
          'headline' => "Item 5",
          'image' => $media_id,
          'copy' => [
            'value' => "Promo unit can be displayed in various column formats",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => "https://www.utexas.edu",
            'title' => "Visit Texas",
          ],
        ],
      ],
      [
        'item' => [
          'headline' => "Item 6",
          'image' => $media_id,
          'copy' => [
            'value' => "Promo units are unlimited",
            'format' => 'restricted_html',
          ],
          'link' => [
            'uri' => "https://www.utexas.edu",
            'title' => "Visit Texas",
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
            'uri' => "https://www.utexas.edu",
            'title' => "Visit Texas",
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
            'uri' => "https://www.utexas.edu",
            'title' => "Visit Texas",
          ],
        ],
      ],
    ]),
  ];
  $block->save();
  $configuration2 = [
    'id' => 'inline_block:utexas_promo_unit',
    'label' => 'Promo Unit',
    'provider' => 'layout_builder',
    'label_display' => '0',
    'block_revision_id' => $block->getRevisionId(),
    'view_mode' => 'utexas_promo_unit',
  ];
  return [
    'block1' => $configuration1,
    'block2' => $configuration2,
  ];
}
