<?php

namespace Drupal\utexas_media_types;

use Drupal\media\Entity\Media;

/**
 * Helper class to give components multiple media type options.
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
   * Get the media file values.
   *
   * @param \Drupal\media\Entity\Media $media
   *   A media object.
   *
   * @return array
   *   An array of media data.
   */
  public static function getFileFieldValue(Media $media) {

    $media_bundle = \Drupal::entityTypeManager()->getStorage('media_type')->load($media->bundle());
    $source_field = $media_bundle->getSource()->getSourceFieldDefinition($media_bundle)->getName();

    return $media->get($source_field)->getValue();

  }

  /**
   * Check if the media is restricted.
   *
   * @param \Drupal\media\Entity\Media $media
   *   A Drupal media object.
   *
   * @return bool
   *   TRUE if the media item is restricted from the current user.
   */
  public static function mediaIsRestricted(Media $media) {
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('utexas_media_access_by_role')) {
      return \Drupal::service('utexas_media_access_by_role.helper')->mediaIsRestricted($media);
    }
    return FALSE;
  }

}
