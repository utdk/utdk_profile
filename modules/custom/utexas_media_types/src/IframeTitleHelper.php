<?php

namespace Drupal\utexas_media_types;

use Drupal\media\OEmbed\Resource;

/**
 * Helper class to provide title attributes for iframes.
 */
class IframeTitleHelper {

  /**
   * Parse the resource title from the object.
   *
   * @param \Drupal\media\OEmbed\Resource $resource
   *   A Drupal-based Media Oembed object.
   *
   * @return string
   *   A printable string.
   */
  public static function getTitle(Resource $resource) {
    return $resource->getProvider()->getName() . ' content: ' . $resource->getTitle();
  }

}
