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

}
