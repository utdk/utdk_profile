<?php

namespace Drupal\layout_builder_content_usability\Controller;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Controller\ControllerBase;
use Drupal\layout_builder\SectionComponent;
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
    $do_save = FALSE;
    $node = $entity_storage->load($id);
    $layout = $node->get('layout_builder__layout')->getValue();
    if (!empty($layout)) {
      foreach ($layout as $section) {
        if (isset($section['section'])) {
          $components = $section['section']->getComponents();
          foreach ($components as $component) {
            if ($component instanceof SectionComponent && $component->getPluginId() === 'inline_block:basic') {
              $configuration = $component->get('configuration');
              $bid = $configuration['block_id'];
              $block = BlockContent::load($bid);
              if (!empty($block)) {
                $content = $block->get('body')->getValue();
                // @todo: inject LLM processing
                $before = $content[0]['value'];
                $after = self::modify($before);
                if ($before !== $after) {
                  $content[0]['value'] = $after;
                  $do_save = TRUE;
                  $block->set('body', $content);
                  $block->setNewRevision();
                  $block->save();
                  $block_storage = \Drupal::service('entity_type.manager')->getStorage('block_content');
                  $latest_revision_id = $block_storage->getLatestRevisionId($bid);
                  if (!empty($latest_revision_id)) {
                    $configuration['block_revision_id'] = $latest_revision_id;
                    $component->setConfiguration($configuration);
                  }
                }
              }
            }
          }
        }
      }
    }
    if ($do_save) {
      $node->set('layout_builder__layout', $layout);
      $node->save();
    }
    return '';
  }

  public static function modify($text) {
    return 'a change';
  }
}
