<?php

namespace Drupal\utexas_google_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\utexas_google_search\Form\UtexasGoogleSearchForm;
use Symfony\Component\HttpFoundation\Request;

/**
 * The route controller for Google PSE Search.
 */
class Search extends ControllerBase {

  /**
   * Creates a render array for the search page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return array
   *   The search form and search results build array.
   */
  public function view(Request $request) {
    $google_pse_id = \Drupal::state()->get('utexas.google_pse_id') ?? '';
    $build['#cache']['contexts'][] = 'url.query_args:keys';
    $search_term = $request->query->get('keys');
    $build['#title'] = !empty($search_term) ? "Search for '$search_term'" : "Search";
    // Add the Google Programmable Search library itself, with ID as a param.
    $build['#attached']['html_head'][] = [
      [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#attributes' => [
          'async' => '',
          'src' => 'https://cse.google.com/cse.js?cx=' . $google_pse_id,
        ],
      ],
      'utexas_google_search_' . $google_pse_id,
    ];
    // Noscript fallback.
    $url = Url::fromUri('https://cse.google.com/cse', [
      'query' => [
        'cx' => $google_pse_id,
        'q' => $search_term,
      ],
    ]);
    $build['#noscript'] = $this->t('@google, or enable JavaScript to view them here.', [
      '@google' => Link::fromTextAndUrl('View the results at Google', $url)->toString(),
    ]);
    $build['search_form'] = $this->formBuilder()->getForm(UtexasGoogleSearchForm::class);
    $build['search_form']['#attributes']['class'][] = 'in-page-google-search';
    $build['search_results'] = [
      '#theme' => 'utexas_google_search_results',
      '#attached' => [
        'library' => [
          'utexas_google_search/accessibilityTweaks',
        ],
      ],
    ];
    return $build;
  }

}
