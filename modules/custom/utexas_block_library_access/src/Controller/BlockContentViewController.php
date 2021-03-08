<?php

namespace Drupal\utexas_block_library_access\Controller;

use Drupal\block_content\Controller\BlockContentController;
use Drupal\views\Views;

/**
 * Controller for the block content add page.
 *
 * Extends normal controller to remove types based on create permission.
 */
class BlockContentViewController extends BlockContentController {

  /**
   * Generate a renderable View based on user input.
   *
   * @return array
   *   A render array.
   */
  public static function buildView() {
    $utprof_profile_listing_view_display = 'page_1';
    /** @var Drupal\views\Views $view */
    $view = Views::getView('block_content');
    if (is_object($view)) {
      // Specify which Views display to use.
      $view->setDisplay($utprof_profile_listing_view_display);
      $view->display_handler->overrideOption('path', 'admin/content/block-content');
      $view->preExecute();
      $view->execute();
      return $view->preview($utprof_profile_listing_view_display);
    }
    return FALSE;
  }

}
