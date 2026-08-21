<?php

namespace Drupal\utnews_view_listing_page\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\utnews_view_listing_page\Form\ListingPageConfig;
use Drupal\views\ViewExecutable;

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
    $terms_to_check = [
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
    foreach ($terms_to_check as $filter => $category) {
      $remove = FALSE;
      $terms = \Drupal::entityQuery('taxonomy_term')
        ->condition('vid', $category['vid'])
        ->accessCheck(TRUE)
        ->execute();
      if (empty($terms)) {
        // There are no taxonomy terms defined. Remove!
        $remove = TRUE;
      }
      else {
        $used_tids = $database->select('node__field_' . $category['field'], 'f')
          ->fields('f', ['field_' . $category['field'] . '_target_id'])
          ->condition('entity_id', array_values($terms), 'IN')
          ->distinct()
          ->execute()
          ->fetchCol();
        if (empty($used_tids)) {
          // There are taxonomy terms, but none are used. Remove!
          $remove = TRUE;
        }
        else {
          foreach (array_values($terms) as $key) {
            // This taxonomy term is not used by any nodes. De-list it!
            if (!in_array($key, $used_tids)) {
              unset($form[$filter]['#options'][$key]);
            }
          }
        }
      }
      if ($remove) {
        $form[$filter]['#access'] = FALSE;
      }
    }
    // If *all* exposed filters are removed, removed the search actions, too.
    $remove_search = TRUE;
    foreach (array_keys($terms_to_check) as $filter) {
      if ($form[$filter]['#access'] !== FALSE) {
        $remove_search = FALSE;
      }
    }
    if ($remove_search) {
      unset($form['actions']);
    }
  }

}
