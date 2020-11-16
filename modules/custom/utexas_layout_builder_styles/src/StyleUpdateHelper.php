<?php

namespace Drupal\utexas_layout_builder_styles;

use Drupal\Core\Database\Database;

/**
 * Use for programmatically adding/removing styles from Layout Builder data.
 */
class StyleUpdateHelper {

  /**
   * Given a block type and a style, add the style to the component, site-wide.
   *
   * @param string $block_type
   *   The machine name of a block type.
   * @param string $style
   *   The machine name of a Layout Builder Style.
   */
  public static function addStyleToBlock($block_type, $style) {
    $connection = Database::getConnection();
    // IMPORTANT: This function is intended to affect *all* node revisions.
    // The use case is limited to when a Layout Builder Style needs to be added
    // retroactively to *preserve* behavior on existing sites.
    $tables = [
      'node__layout_builder__layout',
      'node_revision__layout_builder__layout',
    ];
    foreach ($tables as $table) {
      $query = $connection->query("SELECT * FROM {" . $table . "}");
      $result = $query->fetchAll();
      foreach (array_values($result) as $row) {
        $affected = FALSE;
        $section = unserialize($row->layout_builder__layout_section);
        $components = $section->getComponents();
        foreach (array_values($components) as $component) {
          // If the component is not of the specified block type, skip.
          if (strpos($component->getPluginId(), 'block_content:') === 0) {
            // Check reusable blocks for block type matches.
            $config = $component->get('configuration');
            $uuid = str_replace('block_content:', '', $config['id']);
            $entity = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', $uuid);
            if ($entity->bundle() !== $block_type) {
              continue;
            }
          }
          elseif (strpos($component->getPluginId(), $block_type) === FALSE) {
            // Check inline blocks for block type matches.
            continue;
          }
          $affected = TRUE;
          $additional = $component->get('additional');
          $additional['layout_builder_styles_style'][$style] = $style;
          $component->set('additional', $additional);
        }
        if ($affected) {
          $connection->update($table)
            ->fields([
              'layout_builder__layout_section' => serialize($section),
            ])
            ->condition('entity_id', $row->entity_id, '=')
            ->condition('revision_id', $row->revision_id, '=')
            ->condition('delta', $row->delta, '=')
            ->execute();
          if ($table === 'node__layout_builder__layout') {
            \Drupal::logger('utexas_layout_builder_styles')->notice('Added ' . $style . ' style to node ' . $row->entity_id . ' ' . $block_type . ' instances');
          }
        }
      }
    }
  }

  /**
   * Given a block type and a style, add the style to the component, site-wide.
   *
   * @param string $block_type
   *   The machine name of a block type.
   * @param array $style_map
   *   An array with the view mode machine name as keys and the Layout Builder
   *   Style machine name as values.
   */
  public static function migrateStyleToBlocksFromViewMode($block_type, array $style_map) {
    $connection = Database::getConnection();
    // IMPORTANT: This function is intended to affect *all* node revisions.
    // The use case is limited to when a Layout Builder Style needs to be added
    // retroactively to *preserve* behavior on existing sites.
    $tables = [
      'node__layout_builder__layout',
      'node_revision__layout_builder__layout',
    ];
    foreach ($tables as $table) {
      $query = $connection->query("SELECT * FROM {" . $table . "}");
      $result = $query->fetchAll();
      foreach (array_values($result) as $row) {
        $affected = FALSE;
        /** @var \Drupal\layout_builder\Section $section */
        $section = unserialize($row->layout_builder__layout_section);
        $components = $section->getComponents();
        /** @var \Drupal\layout_builder\SectionComponent $component */
        foreach (array_values($components) as $component) {
          $config = $component->get('configuration');
          // If the component is not of the specified block type, skip.
          if (strpos($component->getPluginId(), 'block_content:') === 0) {
            // Check reusable blocks for block type matches.
            $uuid = str_replace('block_content:', '', $config['id']);
            $entity = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', $uuid);
            if ($entity->bundle() !== $block_type) {
              continue;
            }
          }
          elseif (strpos($component->getPluginId(), $block_type) === FALSE) {
            // Check inline blocks for block type matches.
            continue;
          }

          $view_mode = $config['view_mode'];
          // Check if current view mode exists in view mode/style mapping.
          if (!array_key_exists($view_mode, $style_map)) {
            continue;
          }

          $affected = TRUE;
          $style = $style_map[$view_mode];
          $additional = $component->get('additional');
          $additional['layout_builder_styles_style'][$style] = $style;
          $component->set('additional', $additional);
        }
        if ($affected) {
          $connection->update($table)
            ->fields([
              'layout_builder__layout_section' => serialize($section),
            ])
            ->condition('entity_id', $row->entity_id, '=')
            ->condition('revision_id', $row->revision_id, '=')
            ->condition('delta', $row->delta, '=')
            ->execute();
          if ($table === 'node__layout_builder__layout') {
            \Drupal::logger('utexas_layout_builder_styles')->notice('Converted view modes to Layout Builder Styles in node ' . $row->entity_id . ' ' . $block_type . ' instances.');
          }
        }
      }
    }
  }

}
