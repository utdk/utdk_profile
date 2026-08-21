<?php

namespace Drupal\utnews_view_listing_page\StackMiddleware;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Url;
use Drupal\page_cache\StackMiddleware\PageCache;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Executes the page caching before the main kernel takes over the request.
 *
 * Ignore query parameters also.
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
   * The ignore action.
   *
   * @var string
   */
  protected $ignoreAction = 'ignore';

  /**
   * The cache ID as computed by core.
   *
   * @var string|null
   */
  protected ?string $coreCid = NULL;

  /**
   * {@inheritdoc}
   */
  public function __construct(HttpKernelInterface $http_kernel, CacheBackendInterface $cache, RequestPolicyInterface $request_policy, ResponsePolicyInterface $response_policy, ?EventDispatcherInterface $event_dispatcher = NULL) {
    parent::__construct($http_kernel, $cache, $request_policy, $response_policy);
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * Get defined query parameters.
   *
   * @return array
   *   Ignored params.
   */
  protected function getDefinedQueryParameters() {
    return $this->definedQueryParameters;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCacheId(Request $request) {
    // Once a cache ID is determined for the request, reuse it for the duration
    // of the request. This ensures that when the cache is written, it is only
    // keyed on request data that was available when it was read. For example,
    // the request format might be NULL during cache lookup and then set during
    // routing, in which case we want to key on NULL during writing, since that
    // will be the value during lookups for subsequent requests.
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
    $this->coreCid ??= parent::getCacheId($request);
    // The cid is cached once computed, so it has to be reset to recompute.
    $this->cid = NULL;
    $customCid = $this->getCacheId($request);
    if ($customCid === $this->coreCid) {
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
    $request_query = $this->removeExcludedQueryParameters($request_parts['query']);

    // Allow modules to further normalize the query, e.g. filter values
    // inside bracket arrays like `?f[0]=alias:value` by their prefix.
    if ($this->eventDispatcher !== NULL) {
      $event = new GenericEvent(NULL, [
        'query' => $request_query,
        'parts' => $request_parts,
      ]);
      $this->eventDispatcher->dispatch($event, 'utnews.facet_skip');
      $request_query = (array) $event->getArgument('query');
    }

    if (!empty($request_query)) {
      // Sort the query parameters to minimize cache variants.
      ksort($request_query);
      $request_uri .= '?' . UrlHelper::buildQuery($request_query);
    }

    return $request_uri;
  }

  /**
   * Remove the excluded query parameters.
   *
   * @param array $query_parts
   *   The query parts.
   *
   * @return array
   *   The modified query parts.
   */
  protected function removeExcludedQueryParameters(array $query_parts) {
    return UrlHelper::filterQueryParameters($query_parts, $this->getDefinedQueryParameters());
  }

}
