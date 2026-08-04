<?php

namespace Drupal\utexas_google_search;

use Drupal\search\Entity\SearchPage;

/**
 * Provides helpers for Google CSE migration updates.
 */
class MigrateHelper {

  /**
   * Migrates Google CSE data to UTexas Google Search.
   */
  public static function migrateFromGoogleCse(): void {
    $legacy_configuration = [];
    $default_core_search = '';
    if (\Drupal::moduleHandler()->moduleExists('search') === FALSE) {
      return;
    }
    $search_implementations = SearchPage::loadMultiple();
    foreach ($search_implementations as $search) {

      if ($search->isDefaultSearch()) {
        $default_core_search = $search->id();
      }
      $legacy_configuration[] = $search->id();
      $config = \Drupal::service('config.factory')->getEditable('search.page.' . $search->id());
      $configuration = $search->getPlugin()->getConfiguration();
      if ($search->getPlugin()->getPluginId() !== 'google_cse_search') {
        \Drupal::logger('utexas_google_search')->notice('Migrating Google PSE ID %id', [
          '%id' => $configuration['cx'],
        ]);
        \Drupal::state()->set('utexas.google_pse_id', $configuration['cx']);
      }
      // Delete all Drupal search configurations (admin/config/search/pages).
      $config->delete();
    }
    // Load all search form blocks.
    $search_form_blocks = \Drupal::service('entity_type.manager')
      ->getStorage('block')
      ->loadByProperties(['plugin' => 'search_form_block']);
    foreach ($search_form_blocks as $block) {
      $settings = $block->get('settings');
      // Check for blocks that reference a Google CSE legacy page configuration.
      // If the page_id matches a Google CSE core search configuration,
      // OR if Google CSE is set as the default core search.
      if (empty($settings['page_id'])) {
        $settings['page_id'] = $default_core_search;
      }
      if (in_array($settings['page_id'] ?? NULL, $legacy_configuration, TRUE)) {
        $block_id = $block->id();
        $theme = $block->getTheme();
        $region = $block->getRegion();
        $weight = $block->getWeight();
        $visibility = $block->getVisibility();
        \Drupal::logger('utexas_google_search')->notice('Deleting legacy core search block form %bid', [
          '%bid' => $block->id(),
        ]);
        $block->delete();
        // Create new Utexas Google Search block (/admin/structure/block).
        $new = \Drupal::service('entity_type.manager')
          ->getStorage('block')
          ->create([
            'id' => $block_id,
            'theme' => $theme,
            'region' => $region,
            'weight' => $weight,
            'provider' => 'utexas_google_search',
            'plugin' => 'utexas_google_search',
            'settings' => [
              'id' => 'utexas_google_search',
              'label' => $settings['label'] ?? 'Google Search',
              'label_display' => 0,
              'provider' => 'utexas_google_search',
            ],
            'visibility' => $visibility,
          ]);
        \Drupal::logger('utexas_google_search')->notice('Creating new Utexas Google Search block %bid', [
          '%bid' => $new->id(),
        ]);
        $new->save();
      }
    }
  }

}
