<?php

namespace Drupal\utexas;

use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Helper methods used during installations & updates.
 */
class InstallationHelper {

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
    $image->setFileName(basename($path));
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

  /**
   * Given a module name, check for active dependencies (config or module).
   *
   * @param string $module
   *   The machine name of the module to check.
   *
   * @return bool
   *   Whether or not the module has an active dependency.
   */
  public static function moduleHasNoActiveDependencies($module) {
    $messenger = \Drupal::messenger();
    $t = \Drupal::service('string_translation');
    if (!\Drupal::moduleHandler()->moduleExists($module)) {
      $messenger->addMessage($t->translate('@module is not installed.', ['@module' => $module]));
      return FALSE;
    }
    if (self::moduleHasModuleDependencies($module)) {
      $messenger->addMessage($t->translate('@module has active module dependencies.', ['@module' => $module]));
      return FALSE;
    }
    // Check all configuration for module dependency.
    $config_manager = \Drupal::service('config.manager');
    $dependents = $config_manager->findConfigEntityDependencies('module', [$module]);
    if (!empty($dependents)) {
      $messenger->addMessage($t->translate('@module has active configuration dependencies.', ['@module' => $module]));
      return FALSE;
    }
    $messenger->addMessage($t->translate('@module has no active dependencies and can be uninstalled.', ['@module' => $module]));
    return TRUE;
  }

  /**
   * Given a module name, check if it has active *module* dependencies.
   *
   * @param string $module_to_check
   *   The machine name of the module to check.
   *
   * @return bool
   *   Whether or not the module has an active *module* dependency.
   */
  public static function moduleHasModuleDependencies($module_to_check) {
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = \Drupal::moduleHandler();
    $extensions = $module_handler->getModuleList();
    $installed_modules = array_keys($extensions);
    $extension = \Drupal::service("extension.list.module");
    foreach ($installed_modules as $module) {
      if ($module === $module_to_check) {
        continue;
      }
      $active_module_dependencies = [];
      $info = $extension->getExtensionInfo($module);
      $raw_names = isset($info['dependencies']) && is_array($info['dependencies'])
        ? $info['dependencies']
        : [];
      // Normalize dependency strings to module names.
      foreach ($raw_names as $name) {
        // Remove vendor prefixes (e.g., "drupal:ctools" -> "ctools").
        if (strpos($name, ':') !== FALSE) {
          $parts = explode(':', $name, 2);
          $name = $parts[1];
        }
        // Remove trailing version constraints: "module (>=1.2)" -> "module".
        $name = preg_replace('/\s*\(.*\)\s*$/', '', $name);
        // Trim whitespace just in case.
        $name = trim($name);
        $active_module_dependencies[] = $name;
      }
      if (in_array($module_to_check, $active_module_dependencies)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
