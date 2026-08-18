<?php

declare(strict_types=1);

namespace Drupal\utnews_view_listing_page\EventSubscriber;

use Drupal\views\Ajax\ViewAjaxResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Removes the setBrowserUrl command when AJAX history is disabled.
 *
 * After every AJAX view reload, Drupal's ViewAjaxController adds a
 * setBrowserUrl command to the response that updates the browser address bar
 * via history.replaceState(). This subscriber intercepts the response and
 * strips that command for any view display whose `ajax_history` option has
 * been explicitly set to FALSE.
 *
 * Displays that have never stored the option (e.g. views created before this
 * module was installed) default to TRUE for backward compatibility.
 */
class RemoveQueryParams implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [KernelEvents::RESPONSE => 'onResponse'];
  }

  /**
   * Strips the setBrowserUrl command when ajax_history is disabled.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The kernel response event.
   */
  public function onResponse(ResponseEvent $event): void {
    $response = $event->getResponse();

    if (!$response instanceof ViewAjaxResponse) {
      return;
    }

    $view = $response->getView();
    $id = $view->id();
    if ($id === 'news_overview_page') {
      $commands = &$response->getCommands();
      $commands = array_values(
        array_filter(
          $commands,
          static fn(array $command) => ($command['command'] ?? '') !== 'setBrowserUrl',
        )
      );
    }
  }

}
