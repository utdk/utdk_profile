<?php

namespace Drupal\utnews_view_listing_page\StackMiddleware;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\page_cache\StackMiddleware\PageCache;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Do not use facets' 'f' query parameter in constructing Cache ID for /news.
 */
class FacetCacheSkip extends PageCache {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|null
   */
  protected ?EventDispatcherInterface $eventDispatcher;

  /**
   * The defined query parameters.
   *
   * @var array
   */
  protected $definedQueryParameters = ['f'];

  /**
   * {@inheritdoc}
   */
  public function __construct(HttpKernelInterface $http_kernel, CacheBackendInterface $cache, RequestPolicyInterface $request_policy, ResponsePolicyInterface $response_policy, ?EventDispatcherInterface $event_dispatcher = NULL) {
    parent::__construct($http_kernel, $cache, $request_policy, $response_policy);
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCacheId(Request $request) {
    if (!isset($this->cid)) {
      $cleared = $this->clear($request->getRequestUri());
      $cid_parts = [
        $request->getSchemeAndHttpHost() . $cleared,
        $request->getRequestFormat(NULL),
      ];
      $this->cid = implode(':', $cid_parts);
    }
    return $this->cid;
  }

  /**
   * {@inheritdoc}
   */
  protected function get(Request $request, $allow_invalid = FALSE) {
    $coreCid = parent::getCacheId($request);
    // The cid is cached once computed, so it has to be reset to recompute.
    $this->cid = NULL;
    $customCid = $this->getCacheId($request);
    if ($customCid === $coreCid) {
      // If cids are the same, we can hand over control right away.
      return parent::get($request);
    }
    return parent::get($request);
  }

  /**
   * {@inheritdoc}
   */
  protected function set(Request $request, Response $response, $expire, array $tags) {
    parent::set($request, $response, $expire, $tags);
  }

  /**
   * Clear query string.
   *
   * @param string $value
   *   The value to cleanup.
   *
   * @return string
   *   The cleared value.
   */
  protected function clear($value) {
    $request_parts = UrlHelper::parse($value);
    if (($request_parts['path'] !== '/news')) {
      return $value;
    }
    if (empty($request_parts['query'])) {
      return $value;
    }
    $request_uri = '';
    if (!empty($request_parts['path'])) {
      $request_uri .= $request_parts['path'];
    }
    // Remove the query arguments that are excluded.
    $request_query = UrlHelper::filterQueryParameters($request_parts['query'], $this->definedQueryParameters);
    if (!empty($request_query)) {
      // Sort the query parameters to minimize cache variants.
      ksort($request_query);
      $request_uri .= '?' . UrlHelper::buildQuery($request_query);
    }
    return $request_uri;
  }

}
