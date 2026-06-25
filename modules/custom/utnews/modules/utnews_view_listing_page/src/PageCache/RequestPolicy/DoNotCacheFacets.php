<?php

namespace Drupal\utnews_view_listing_page\PageCache\RequestPolicy;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Skip caching of requests with Search API Facets.
 */
class DoNotCacheFacets implements RequestPolicyInterface {

  /**
   * {@inheritdoc}
   */
  public function check(Request $request) {
    $facet_keys = [
      'f',
    ];
    $request_parts = UrlHelper::parse($request->getRequestUri());
    if ($request_parts['path'] === '/news') {
      if (!empty($request_parts['query'])) {
        foreach (array_keys($request_parts['query']) as $key) {
          if (in_array($key, $facet_keys)) {
            return static::DENY;
          }
        }
      }
    }
    // Returns NULL if the policy is not specified for the given response.
    return NULL;
  }

}
