<?php

namespace Drupal\utexas_flex_content_area\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;

/**
 * Plugin implementation of the 'utexas_flex_content_area' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_flex_content_area",
 *   label = @Translation("Flex Content Area (Two per row)"),
 *   field_types = {
 *     "utexas_flex_content_area"
 *   }
 * )
 */
class UTexasFlexContentAreaDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $responsive_image_style_name = 'utexas_responsive_image_fca';
    // Collect cache tags to be added for each item in the field.
    $responsive_image_style = \Drupal::entityTypeManager()->getStorage('responsive_image_style')->load($responsive_image_style_name);
    $image_styles_to_load = [];
    $cache_tags = [];
    if ($responsive_image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $responsive_image_style->getCacheTags());
      $image_styles_to_load = $responsive_image_style->getImageStyleIds();
    }
    $image_styles = \Drupal::entityTypeManager()->getStorage('image_style')->loadMultiple($image_styles_to_load);
    foreach ($image_styles as $image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $image_style->getCacheTags());
    }
    foreach ($items as $delta => $item) {
      if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($item->image)) {
        // Format image.
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
        $image_render_array = [];
        if ($file = \Drupal::entityTypeManager()->getStorage('file')->load($media_attributes[0]['target_id'])) {
          $image = new \stdClass();
          $image->title = NULL;
          $image->alt = $media_attributes[0]['alt'];
          $image->entity = $file;
          $image->width = NULL;
          $image->height = NULL;
          $image_render_array = [
            '#theme' => 'responsive_image_formatter',
            '#item' => $image,
            '#item_attributes' => [
              'class' => 'ut-img--fluid',
            ],
            '#responsive_image_style_id' => $responsive_image_style_name,
            '#cache' => [
              'tags' => $cache_tags,
            ],
          ];
        }
        // Add the file entity to the cache dependencies.
        // This will clear our cache when this entity updates.
        $renderer = \Drupal::service('renderer');
        $renderer->addCacheableDependency($image_render_array, $file);
      }
      else {
        $image_render_array = [];
      }
      // Format headline.
      $headline = $item->headline ?? '';
      // Format links.
      $links = unserialize($item->links);
      if (!empty($links)) {
        foreach ($links as $link) {
          if (!empty($link['title'])) {
            $url = Url::fromUri($link['url']);
            $url->setAbsolute();
            $link = Link::fromTextAndUrl($link['title'], $url);
          }
          // Ensure that links without title text print the URL.
          else {
            $url = Url::fromUri($link['url']);
            $url->setAbsolute();
            $link['title'] = $url->toString();
          }
        }
      }
      else {
        $links = [];
      }
      // Format CTA.
      if (!empty($item->link_uri)) {
        $url = Url::fromUri($item->link_uri);
        $link = $url->toString();
        if (isset($item->headline)) {
          $headline = Link::fromTextAndUrl($item->headline, Url::fromUri($item->link_uri));
        }
        if (empty($item->link_text)) {
          $url->setAbsolute();
          $item->link_text = $url->toString();
        }
        $link_options = [
          'attributes' => [
            'class' => [
              'ut-btn--small',
            ],
          ],
        ];
        $url->setOptions($link_options);
        $cta = Link::fromTextAndUrl($item->link_text, $url);
      }
      $elements[] = [
        '#theme' => 'utexas_flex_content_area',
        '#image' => $image_render_array,
        '#headline' => $headline,
        '#copy' => check_markup($item->copy_value, $item->copy_format),
        '#links' => $links,
        '#cta' => $cta ?? '',
      ];
      $elements['#items'][$delta] = new \StdClass();
      $elements['#items'][$delta]->_attributes = [
        'class' => ['ut-flex-content-area'],
      ];
      $elements['#attributes']['class'][] = 'ut-flex-content-area-wrapper';
    }
    $elements['#attached']['library'][] = 'utexas_flex_content_area/flex-content-area';
    return $elements;

  }

}
