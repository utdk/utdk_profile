<?php

namespace Drupal\utexas_layouts\EventSubscriber;

use Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent;
use Drupal\layout_builder\LayoutBuilderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alters render arrays for all block components.
 */
class BlockComponentRenderArray implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[LayoutBuilderEvents::SECTION_COMPONENT_BUILD_RENDER_ARRAY] = ['onBuildRender'];
    return $events;
  }

  /**
   * Add region key/value on the event.
   *
   * Due to event subscriber weighting, note that this subscriber is lower in
   * priority than the event subscriber in the layout_builder module.
   * onBuildRender() has previously been "fired" by that class.
   * see \Drupal\layout_builder\EventSubscriber\BlockComponentRenderArray.
   *
   * @param \Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent $event
   *   The section component render event.
   */
  public function onBuildRender(SectionComponentBuildRenderArrayEvent $event) {
    $region = $event->getComponent()->getRegion();

    // Get the current "build," add the key/value and reset the "build".
    $build = $event->getBuild();

    // If the build is empty, the block produced no renderable content (e.g.,
    // access denied or empty output). Core's subscriber returns early in that
    // case without calling setBuild(), leaving $build as []. Adding
    // #utexas_layouts_region to an empty array causes NavigationLayout::build()
    // to treat the component as non-empty (Element::isEmpty() only ignores
    // #cache and #weight keys), which results in '#theme' => 'block__navigation'
    // being applied to a render array lacking the required 'content' key,
    // triggering PHP warnings in BlockHooks::preprocessBlock() and
    // BlockContentHooks::themeSuggestionsBlockAlter().
    if (empty($build)) {
      return;
    }

    $build['#utexas_layouts_region'] = $region;
    $event->setBuild($build);
  }

}
