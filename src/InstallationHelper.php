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

  /**
   * Convert incorrectly migrated metatags robots array to string.
   */
  public static function normalizeRobotsMetatags() {
    $connection = \Drupal::database();
    // Fix both the current node data and all revisions.
    $tables = [
      'node_revision__field_flex_page_metatags',
      'node__field_flex_page_metatags',
    ];
    foreach ($tables as $table) {
      $query = $connection->select($table, 'n');
      $query->fields('n', [
        'entity_id',
        'revision_id',
        'delta',
        'field_flex_page_metatags_value',
      ]);
      $result = $query->execute();
      $results = $result->fetchAll();
      if (!$results || empty($results)) {
        continue;
      }
      foreach ($results as $metatags) {
        $metatags_array = unserialize($metatags->field_flex_page_metatags_value);
        if (!isset($metatags_array['robots'])) {
          // There are no robots declarations. Move on.
          continue;
        }
        if (!is_array($metatags_array['robots'])) {
          // The data is already in the correct string format. Move on.
          continue;
        }
        $new_robots = [];
        // Retrieve any robots declarations that are not 0 and put them in a
        // comma-separated string.
        // Previous format ['nofollow' => 'nofollow', 'noindex' => 'noindex'].
        // New format: "nofollow, noindex".
        foreach ($metatags_array['robots'] as $key => $value) {
          if ($value !== 0) {
            $new_robots[] = $key;
          }
        }
        if (!empty($new_robots)) {
          $metatags_array['robots'] = implode(", ", $new_robots);
        }
        else {
          unset($metatags_array['robots']);
        }
        // Save the new format to the database.
        $new_metatags = serialize($metatags_array);
        $connection->update($table)
          ->fields([
            'field_flex_page_metatags_value' => $new_metatags,
          ])
          ->condition('entity_id', $metatags->entity_id, '=')
          ->condition('revision_id', $metatags->revision_id, '=')
          ->condition('delta', $metatags->delta, '=')
          ->execute();
      }
    }
  }

}
