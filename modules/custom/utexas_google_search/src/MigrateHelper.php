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
    $search_implementations = SearchPage::loadMultiple();
    foreach ($search_implementations as $search) {
      if ($search->getPlugin()->getPluginId() !== 'google_cse_search') {
        continue;
      }
      if ($search->isDefaultSearch()) {
        $default_core_search = $search->id();
      }
      $legacy_configuration[] = $search->id();
      $config = \Drupal::service('config.factory')->getEditable('search.page.' . $search->id());
      $configuration = $search->getPlugin()->getConfiguration();
      // Add Utexas Google Search setting.
      \Drupal::state()->set('utexas.google_pse_id', $configuration['cx']);
      // Delete Google CSE Drupal search config (admin/config/search/pages).
      $config->delete();
    }
    $legacy_search_blocks = [];
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
        $legacy_search_blocks[] = $block->id();
        // Create new Utexas Google Search block (/admin/structure/block).
        $new = \Drupal::service('entity_type.manager')
          ->getStorage('block')
          ->create([
            'id' => $block->id() . '_google_search',
            'theme' => $block->getTheme(),
            'region' => $block->getRegion(),
            'weight' => $block->getWeight(),
            'provider' => 'utexas_google_search',
            'plugin' => 'utexas_google_search',
            'settings' => [
              'id' => 'utexas_google_search',
              'label' => $settings['label'] ?? 'Google Search',
              'label_display' => 'hidden',
              'provider' => 'utexas_google_search',
            ],
            'visibility' => $block->getVisibility(),
          ]);
        $new->save();
        // Delete legacy block core search form block (/admin/structure/block).
        $block->delete();
      }
    }
  }

}
