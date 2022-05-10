<?php

namespace Drupal\utexas;

use Drupal\block\Entity\Block;

/**
 * Helper methods used during installations & updates.
 */
class InstallationHelper {

  /**
   * Helper function to place AddToAny block.
   */
  public static function addSocialSharing() {
    $moduleHandler = \Drupal::service('module_handler');
    // Only add if the addtoany module is enabled.
    if (!$moduleHandler->moduleExists('addtoany')) {
      return;
    }
    $blockEntityManager = \Drupal::entityTypeManager()->getStorage('block');
    $block = $blockEntityManager->create([
      'id' => 'addtoany_utexas',
      'settings' => [
        'label' => 'Share this content',
        'provider' => 'addtoany',
        'label_display' => 'visible',
      ],
      'plugin' => 'addtoany_block',
      'theme' => \Drupal::configFactory()->getEditable('system.theme')->get('default'),
    ]);
    $block->setRegion('content');

    $weight = 0;
    // Place this block directly above the main content.
    if ($page_title = Block::load('main_page_content')) {
      $weight = $page_title->getWeight();
      $weight = $weight - 1;
    }
    $block->setWeight($weight);
    $block->enable();
    $block->setVisibilityConfig("entity_bundle:node", [
      'bundles' => [
        'page' => 'page',
      ],
      'negate' => FALSE,
      'context_mapping' => [
        'node' => '@node.node_route_context:node',
      ],
    ]);
    $block->save();
  }

}
