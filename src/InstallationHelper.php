<?php

namespace Drupal\utexas;

use Drupal\block\Entity\Block;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Symfony\Component\Yaml\Yaml;

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
   * Import a default image file for use with metatags.
   *
   * @return object
   *   The file associated with this image.
   */
  public static function uploadDefaultOgImage() {
    /** @var \Drupal\file\FileRepositoryInterface $file_repository */
    $file_repository = \Drupal::service('file.repository');
    $file_system = \Drupal::service('file_system');
    $filedir = 'public://opengraph_images';
    $file_system->prepareDirectory($filedir, FileSystemInterface::CREATE_DIRECTORY);
    $path = \Drupal::service('extension.list.profile')->getPath('utexas') . '/assets/ut_tower.jpg';
    $image = File::create();
    $image->setFileUri($path);
    $image->setOwnerId(\Drupal::currentUser()->id());
    $image->setMimeType(\Drupal::service('file.mime_type.guesser')->guessMimeType($path));
    $image->setFileName($file_system->basename($path));
    $destination_dir = 'public://opengraph_images';
    $file_system->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY);
    $destination = $destination_dir . '/' . basename($path);
    $file = $file_repository->copy($image, $destination);
    return $file;
  }

  /**
   * Populate default 'Global' metatags.
   */
  public static function populateDefaultMetatags() {
    $defaults = [
      'canonical_url' => '[current-page:url]',
      'og_title' => '[current-page:title]',
      'og_type' => 'website',
      'og_updated_time' => '[node:changed:custom:c]',
      'og_url' => '[current-page:url]',
      'title' => '[current-page:title] | [site:name]',
      'twitter_cards_type' => 'summary',
      'twitter_cards_title' => '[current-page:title]',
    ];
    $metatags = \Drupal::configFactory()
      ->getEditable('metatag.metatag_defaults.global');
    $tags = $metatags->get('tags');
    // Remove deprecated twitter_cards_page_url.
    if (isset($tags['twitter_cards_page_url'])) {
      unset($tags['twitter_cards_page_url']);
    }
    foreach ($defaults as $key => $value) {
      $tags[$key] = $value;
      \Drupal::logger('utexas')->notice("Setting default metatag for $key..");
    }
    // For sites which have not yet set a global OG image, set one.
    if (!isset($tags['og_image'])) {
      \Drupal::logger('utexas')->notice('Setting default global OG image...');
      $og_image = self::uploadDefaultOgImage();
      // Set the file status to 'permanent'.
      \Drupal::service('file.usage')->add($og_image, 'utexas', 'file', $og_image->id());
      \Drupal::state()->set('default_og_image', $og_image->id());
      $uri = $og_image->getFileUri();
      $filepath = \Drupal::service('file_url_generator')->generateString($uri);
      $default_og_image = $filepath;
      $tags['og_image'] = $default_og_image;
    }
    \Drupal::configFactory()
      ->getEditable('metatag.metatag_defaults.global')
      ->set('tags', $tags)
      ->save(TRUE);
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
        // This serialized data is trusted from the component,
        // so we do not restrict object types in unserialize().
        // phpcs:ignore
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

  /**
   * Set /admin/people configuration to UT Drupal Kit default.
   */
  public static function configurePeopleView() {
    $config_name = 'views.view.user_admin_people';
    $config = \Drupal::configFactory()->getEditable($config_name);
    $config_path = \Drupal::service('extension.list.profile')->getPath('utexas') . '/config/default/' . $config_name . '.yml';
    if (!empty($config_path)) {
      $data = Yaml::parse(file_get_contents($config_path));
      if (is_array($data)) {
        $config->setData($data)->save(TRUE);
      }
    }
  }

}
