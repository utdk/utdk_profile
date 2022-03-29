<?php

namespace Drupal\utexas_media_types;

/**
 * Helper class to give components mulitple image type options.
 */
class MediaEntityImageHelper {

  /**
   * Get the allowed media type bundles.
   */
  public static function getAllowedBundles() {

    $allowed_bundles = [];
    $media_bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('media');

    /** @var \Drupal\media\MediaTypeInterface $media_type */
    $media_type_manager = \Drupal::entityTypeManager()->getStorage('media_type');

    foreach (array_keys($media_bundles) as $bundle) {
      $source = $media_type_manager->load($bundle)->getSource()->getPluginId();
      if ($source === 'image') {
        $allowed_bundles[] = $bundle;
      }
    }

    return !empty($allowed_bundles) ? $allowed_bundles : 0;

  }

  /**
   * Get the media attributes.
   */
  public static function getMediaAttributes($media) {

    $media_bundle = $this->entityTypeManager->getStorage('media_type')->load($media->bundle());
    $source_field = $media_bundle->getSource()->getSourceFieldDefinition($media_bundle)->getName();

    return $source_field;

  }

}
