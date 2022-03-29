<?php

namespace Drupal\utexas_media_types;

/**
 * Helper class to give components mulitple image type options.
 */
class MediaEntityImageHelper {

  /**
   * Get the allowed media type bundles.
   *
   * @return array
   *   An array of media types.
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

    return $allowed_bundles;

  }

  /**
   * Get the media attributes.
   *
   * @param \Drupal\Core\Entity\EntityBase $media
   *   A media object. (No idea what object this is! Is this correct?)
   *
   * @return array
   *   An array of media data.
   */
  public static function getMediaAttributes(\Drupal\Core\Entity\EntityBase $media) {

    $media_bundle = \Drupal::entityTypeManager()->getStorage('media_type')->load($media->bundle());
    $source_field = $media_bundle->getSource()->getSourceFieldDefinition($media_bundle)->getName();

    return $source_field;

  }

}
