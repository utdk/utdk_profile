<?php

/**
 * @file
 * Hooks and documentation related to the UTexas profile.
 */

use Drupal\block_content\Entity\BlockContent;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Hook into the demo content action during installation.
 *
 * @see utexas_install_batch_processing
 */
function hook_utexas_demo_content() {
  // In this example, a "Basic" block would be saved to the database.
  $block = BlockContent::create([
    'info' => 'block 1',
    'type' => 'basic',
    'langcode' => 'en',
  ]);
  $block->save();
}

/**
 * @} End of "addtogroup hooks".
 */
