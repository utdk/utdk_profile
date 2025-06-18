<?php

namespace Drupal\layout_builder_content_usability\Controller;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Controller\ControllerBase;
use Drupal\layout_builder\SectionComponent;
use Drupal\node\NodeInterface;
use GuzzleHttp\Client;

/**
 * Corpus Search endpoint.
 *
 * @package Drupal\corpus_search\Controller
 */
class UsabilityController extends ControllerBase {

  public function fix(NodeInterface $node) {
    $id = $node->id();
    $result = self::revise($id);
    $build = [
      '#markup' => 'Processed!',
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
              $bid = $configuration['block_revision_id'];
              //$block = BlockContent::loadRevision($bid);
              $block = \Drupal::entityTypeManager()
                ->getStorage('block_content')
                ->loadRevision($bid);
              if (!empty($block)) {
                $content = $block->get('body')->getValue();
                $before = $content[0]['value'];
                $after = self::modify($before);
                //echo '<h2>Before</h2>';
                //echo '<pre>';
                //echo $before;
                //echo '</pre>';
                //echo '<h2>After</h2>';
                //echo '<pre>';
                //echo $after;
                //echo '</pre>';
                if ($before !== $after) {
                  $content[0]['value'] = $after;
                  $do_save = TRUE;
                  $block->set('body', $content);
                  $block->setNewRevision();
                  $block->save();
                  $block_storage = \Drupal::service('entity_type.manager')->getStorage('block_content');
                  $latest_revision_id = $block_storage->getLatestRevisionId($configuration['block_id']);
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
    $secret = \Drupal::state()->get('layout_builder_content_usability_secret') ?? '';
    $client = new Client();

    $response = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent', [
      'query' => [
        'key' => $secret,
      ],
      'headers' => [
        'Content-Type' => 'application/json'
      ],
      'json' => [
        'contents' => [
          [
            'parts' => [
              [
                'text' => 'Restructure the HTML defined between the tokens [targetstart] and [targetend] to consist of multiple paragraph tags that contain no more than 200 characters of text each. Where a list of items seems to be present in paragraph text, convert it to an HTML unordered list. Minimize changing the text itself and make every paragraph consist of one or more complete sentences. Do not encapsulate the output in backticks. Here is the content: [targetstart]' . $text . '[targetend]',
              ]
            ]
          ]
        ]
      ]
    ]);

    $body = json_decode($response->getBody()->getContents());
    return $body->candidates[0]->content->parts[0]->text;
  }

}
