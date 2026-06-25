<?php

namespace Drupal\utnews_view_listing_page\Plugin\views\field;

use Drupal\search_api\Plugin\views\field\SearchApiRenderedItem;
use Drupal\views\ResultRow;
use Drupal\views\Attribute\ViewsField;

/**
 * Provides a Views field handler to render a Search API item.
 */
#[ViewsField('utnews_listing_search_api')]
class NewsListingSearchApi extends SearchApiRenderedItem {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $build = parent::render($values);
    $config = \Drupal::config('utnews_view_listing_page.config');
    if ((int) $config->get('display_date') === 0) {
      $build['#display_date'] = FALSE;
    }
    if ((int) $config->get('display_summary') === 0) {
      $build['#display_summary'] = FALSE;
    }
    if ((int) $config->get('display_thumbnail') === 0) {
      $build['#display_thumbnail'] = FALSE;
    }
    $build['#cache']['tags'][] = 'config:utnews_view_listing_page.config';
    return $build;
  }

}
