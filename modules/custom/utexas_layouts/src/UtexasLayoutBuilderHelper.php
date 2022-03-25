<?php

namespace Drupal\utexas_layouts;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Identify information about Layout Builder-enabled pages.
 */
class UtexasLayoutBuilderHelper {

  /**
   * Whether the first section of this Layout Builder page is "Readable width".
   *
   * @return bool
   *   Whether the first section of this Layout Builder page is "Readable".
   */
  public static function firstSectionIsReadable() {
    $entity = self::getRouteEntity();
    $entity_type = $entity ? $entity->getEntityTypeId() : NULL;
    $bundle = $entity ? $entity->bundle() : NULL;
    if (!$entity_type || !$bundle) {
      return FALSE;
    }
    if ($entity->hasField('layout_builder__layout')) {
      $sections = $entity->get('layout_builder__layout')->getSections();
      if (isset($sections[0])) {
        $settings = $entity->get('layout_builder__layout')->getSection(0)->getLayoutSettings();
        if (isset($settings['section_width']) && $settings['section_width'] === 'readable') {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Retrieve the Drupal route entity.
   *
   * @return mixed
   *   A Drupal entity object, or NULL.
   */
  public static function getRouteEntity() {
    $route_match = \Drupal::routeMatch();
    // Entity will be found in the route parameters.
    if (($route = $route_match->getRouteObject()) && ($parameters = $route->getOption('parameters'))) {
      // Determine if the current route represents an entity.
      foreach ($parameters as $name => $options) {
        if (isset($options['type']) && strpos($options['type'], 'entity:') === 0) {
          $entity = $route_match->getParameter($name);
          if ($entity instanceof ContentEntityInterface && $entity->hasLinkTemplate('canonical')) {
            return $entity;
          }
          // Since entity was found, no need to iterate further.
          return NULL;
        }
      }
    }
  }

}
