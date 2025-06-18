<?php

namespace Drupal\layout_builder_content_usability\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;

/**
 * Corpus Search endpoint.
 *
 * @package Drupal\corpus_search\Controller
 */
class UsabilityController extends ControllerBase {

  public function fix(NodeInterface $node) {
    $id = $node->id();
    self::revise($id);
    $build = [
      '#markup' => $id,
    ];
    return $build;
  }

  public static function revise($id) {
    $entity_storage = \Drupal::entityTypeManager()->getStorage('node');
    /** @var \Drupal\node\NodeInterface $node */
    $node = $entity_storage->load($id);
    $connection = \Drupal::database();
    // Get a list of all block revision IDs in the system.
    $query = $connection->select('block_content_field_revision', 'b');
    $query->fields('b', ['revision_id']);
    $result = $query->execute();
    $block_revisions = $result->fetchCol();
    $existing_block_revisions = array_values($block_revisions);

    // Get all *current* Layout Builder layouts.
    $query = $connection->select('node__layout_builder__layout', 'n');
    $query->condition('n.bundle', 'utexas_flex_page', '=');
    $query->condition('n.entity_id', $id, '=');
    $query->fields('n', ['entity_id', 'layout_builder__layout_section']);
    $result = $query->execute();
    $layouts = $result->fetchAll();

    foreach ($layouts as $layout) {
      $section = unserialize($layout->layout_builder__layout_section);
      $components = $section->getComponents();
      foreach ($components as $component) {
        $plugin = $component->getPlugin();
        $component_array = $component->toArray();
        if (isset($component_array['configuration']['block_revision_id'])) {
          print_r($layout->entity_id);
          print_r($component_array['configuration']['label']);
        }
      }
    }
  }
}
