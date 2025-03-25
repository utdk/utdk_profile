<?php

namespace Drupal\utexas;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\layout_builder\Entity\LayoutBuilderEntityViewDisplay;

/**
 * Business logic for theme processing.
 */
class ThemeHelper {

  /**
   * Whether this is a Layout Builder page.
   *
   * @return bool
   *   Whether this is a Layout Builder page.
   */
  public static function isLayoutBuilderPage() {
    $entity = self::getRouteEntity();
    $entity_type = $entity ? $entity->getEntityTypeId() : NULL;
    $bundle = $entity ? $entity->bundle() : NULL;
    if ($entity_type && $bundle) {
      $display = \Drupal::entityTypeManager()
        ->getStorage('entity_view_display')
        ->load($entity_type . '.' . $bundle . '.default');
      if (!$display) {
        return FALSE;
      }
      if ($display instanceof LayoutBuilderEntityViewDisplay && $display->isLayoutBuilderEnabled()) {
        return TRUE;
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
