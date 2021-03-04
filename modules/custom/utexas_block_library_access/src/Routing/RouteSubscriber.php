<?php

namespace Drupal\utexas_block_library_access\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The AccessControlHandler class name.
   *
   * @var string
   */
  private $accessControlHandlerClassMethod = 'utexas_block_library_access.access_control_handler::checkBlockContentAccess';

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Change access and controller callback for the block content add page.
    if ($route = $collection->get('block_content.add_page')) {
      $route->addRequirements([
        '_custom_access' => $this->accessControlHandlerClassMethod,
      ]);
      $route->setDefault(
        'operation',
        'update'
      );
      // Remove required "administer blocks" permission.
      $this->removePermissionRequirement($route);
    }

    // Change access callback for the block content add forms.
    if ($route = $collection->get('block_content.add_form')) {
      $route->addRequirements([
        '_custom_access' => $this->accessControlHandlerClassMethod,
      ]);
      $route->setDefault(
        'operation',
        'update'
      );
      // Remove required "administer blocks" permission.
      $this->removePermissionRequirement($route);
    }
  }

  /**
   * Remove required "administer blocks" permission from route.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The Route object.
   */
  private function removePermissionRequirement(Route $route) {
    if ($route->hasRequirement('_permission')) {
      $requirements = $route->getRequirements();
      unset($requirements['_permission']);
      $route->setRequirements($requirements);
    }
  }

}
