<?php

namespace Drupal\utexas_block_library_access\Plugin\Menu\LocalAction;

use Drupal\block_content\Plugin\Menu\LocalAction\BlockContentAddLocalAction;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;

/**
 * Modifies the 'Add custom block' local action.
 */
class UtexasBlockContentAddLocalAction extends BlockContentAddLocalAction {

  /**
   * {@inheritdoc}
   */
  public function getOptions(RouteMatchInterface $route_match) {
    $options = parent::getOptions($route_match);
    // Adds a destination on custom block listing.
    if ($route_match->getRouteName() == 'utexas_block_library_access.entity.block_content.collection') {
      $options['query']['destination'] = Url::fromRoute('<current>')->toString();
    }
    return $options;
  }

}
