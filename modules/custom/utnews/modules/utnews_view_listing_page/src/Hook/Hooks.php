<?php

namespace Drupal\utnews_view_listing_page\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\utnews_view_listing_page\Form\ListingPageConfig;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_form_FORM_ID_alter() for the general config form.
   */
  #[Hook('form_utnews_general_config_alter')]
  public function formUtnewsGeneralConfigAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Alter the general config form to include the listing page title.
    \Drupal::classResolver(ListingPageConfig::class)->alterForm($form, $form_state, $form_id);
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_page__news')]
  public function preprocessPageNews(&$variables) {
    // Styling variables.
    $variables['attributes']['class'][] = 'utexas-field-border';
    $variables['attributes']['class'][] = 'utexas-field-background';
    $variables['page']['sidebar_first_width'] = 'col-md-4';
  }

  /**
   * Implements hook_plugin_filter_TYPE__CONSUMER_alter().
   */
  #[Hook('plugin_filter_block__layout_builder_alter')]
  public function pluginFilterBlockLayoutBuilderAlter(array &$definitions) {
    // News facets are not designed to be placed with Layout Builder.
    // Suppress them from that context.
    unset($definitions['facet_block:author']);
    unset($definitions['facet_block:categories']);
    unset($definitions['facet_block:tags']);
    unset($definitions['facets_summary_block:utnews']);
  }

  /**
   * Implements hook_views_data_alter().
   */
  #[Hook('views_data_alter')]
  public function viewsDataAlter(array &$data) {
    $data['search_api_index_utnews']['utnews_listing_search_api'] = [
      'title' => $this->t('News Listing dynamic view mode'),
      'field' => [
        'title' => $this->t('News Listing (Search API)'),
        'help' => $this->t('Render news article with configurable settings for summary, image, and date'),
        'id' => 'utnews_listing_search_api',
      ],
    ];
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for views_exposed_form.
   */
  #[Hook('form_views_exposed_form_alter')]
  public function formViewsExposedFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    $view = $form_state->getStorage()['view'];
    if ($view->id() !== 'utnews_listing_page') {
      return;
    }
    $filters_to_check = [
      'author' => [
        'vid' => 'utnews_authors',
        'field' => 'utnews_article_author',
      ],
      'category' => [
        'vid' => 'utnews_categories',
        'field' => 'utnews_news_categories',
      ],
      'tags' => [
        'vid' => 'utnews_tags',
        'field' => 'utnews_news_tags',
      ],
    ];
    $database = \Drupal::database();
    // For performance, we do a single query to build a database of
    // news taxonomy terms by VID.
    $taxonomy_data = $database->select('taxonomy_term_data', 'f')
      ->fields('f', ['tid', 'vid'])
      ->condition('vid', ['utnews_authors', 'utnews_categories', 'utnews_tags'], 'IN')
      ->distinct()
      ->execute()
      ->fetchAllAssoc('tid');
    $utnews_taxonomies = [
      'utnews_authors' => [],
      'utnews_categories' => [],
      'utnews_tags' => [],
    ];
    foreach ($taxonomy_data as $tid => $values) {
      $vid = $values->vid;
      $utnews_taxonomies[$vid][] = $tid;
    }
    // Get a list of *published* News article nids.
    $published_news = $database->select('node_field_data', 'n')
      ->fields('n', ['nid'])
      ->condition('status', '1')
      ->condition('deleted', NULL, 'IS NULL')
      ->condition('type', 'utnews_news')
      ->execute()
      ->fetchCol(0);
    foreach ($filters_to_check as $filter => $category) {
      $vid = $category['vid'];
      if (empty($utnews_taxonomies[$vid]) || empty($published_news)) {
        // There are zero taxonomy terms in this vocab, so we can skip the
        // filter altogether without further processing.
        $form[$filter]['#access'] = FALSE;
      }
      else {
        // There *are* taxonomy terms, so we *may* need to limit them.
        $vid = $category['vid'];
        // Get any taxonomy terms associated with published nodes.
        $used_tids = $database->select('node__field_' . $category['field'], 'f')
          ->fields('f', ['field_' . $category['field'] . '_target_id'])
          ->condition('entity_id', $published_news, 'IN')
          ->execute()
          ->fetchCol(0);
        if (empty($used_tids)) {
          // There are taxonomy terms, but none are used. Do not display!
          $form[$filter]['#access'] = FALSE;
        }
        else {
          foreach ($utnews_taxonomies[$vid] as $tid) {
            if (!in_array($tid, $used_tids)) {
              unset($form[$filter]['#options'][$tid]);
            }
          }
          $counts = array_count_values($used_tids);
          if (count($counts) > 99) {
            // The number of used taxonomy terms is large enough to risk a
            // problem with the pantheon_advanced_page_cache module. See
            // https://github.com/utexas-wcms-contracts/utexas-lase/issues/30
            // Cap the number at 100.
            $limited = array_slice($counts, 0, 99, $preserve_keys = TRUE);
            foreach (array_keys($form[$filter]['#options']) as $key) {
              // Always retain the 'All' option.
              if (!in_array($key, array_keys($limited)) && $key !== 'All') {
                unset($form[$filter]['#options'][$key]);
              }
            }
          }
        }
      }
    }
    $remove_search = 0;
    foreach (array_keys($filters_to_check) as $filter) {
      if (isset($form[$filter]['#access']) && $form[$filter]['#access'] === FALSE) {
        $remove_search++;
      }
    }
    if ($remove_search === 3) {
      // *All* exposed filters are removed, so remove the search actions, too.
      unset($form['actions']);
    }
  }

}
