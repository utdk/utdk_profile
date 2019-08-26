<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_hero_4",
 *   label = @Translation("Style 4: Centered image with dark bottom pane containing heading, subheading and call-to-action"),
 *   field_types = {
 *     "utexas_hero"
 *   }
 * )
 */
class UTexasHeroStyle4Formatter extends UTexasHeroFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $responsive_image_style_name = 'utexas_responsive_image_hi';
    $responsive_image_style = $this->entityTypeManager->getStorage('responsive_image_style')->load($responsive_image_style_name);
    $image_styles_to_load = [];
    $cache_tags = [];
    if ($responsive_image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $responsive_image_style->getCacheTags());
      $image_styles_to_load = $responsive_image_style->getImageStyleIds();
    }
    $image_styles = $this->entityTypeManager->getStorage('image_style')->loadMultiple($image_styles_to_load);
    foreach ($image_styles as $image_style) {
      $cache_tags = Cache::mergeTags($cache_tags, $image_style->getCacheTags());
    }

    foreach ($items as $item) {
      $cta['title'] = '';
      $cta['uri'] = '';
      if (!empty($item->link_uri)) {
        $url = Url::fromUri($item->link_uri);
        $cta['uri'] = $url;
        if (empty($item->link_title)) {
          $url = Url::fromUri($item->link_uri);
          $url->setAbsolute();
          $cta['title'] = $url->toString();
        }
        else {
          $cta['title'] = $item->link_title;
        }
      }
      $image_render_array = [];
      if ($media = $this->entityTypeManager->getStorage('media')->load($item->media)) {
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
        if ($file = $this->entityTypeManager->getStorage('file')->load($media_attributes[0]['target_id'])) {
          $image = new \stdClass();
          $image->title = NULL;
          $image->alt = $media_attributes[0]['alt'];
          $image->entity = $file;
          $image->uri = $file->getFileUri();
          $image->width = NULL;
          $image->height = NULL;
          // Check if image styles have been disabled (e.g., animated GIF)
          if (!$item->disable_image_styles) {
            $image_render_array = [
              '#theme' => 'responsive_image_formatter',
              '#item' => $image,
              '#item_attributes' => [],
              '#responsive_image_style_id' => $responsive_image_style_name,
              '#cache' => [
                'tags' => $cache_tags,
              ],
            ];
          }
          else {
            $image_render_array = [
              '#theme' => 'image',
              '#uri' => $image->uri,
              '#alt' => $image->alt,
            ];
          }
        }
      }
      $elements[] = [
        '#theme' => 'utexas_hero_4',
        '#media' => $image_render_array,
        '#heading' => $item->heading,
        '#subheading' => $item->subheading,
        '#caption' => $item->caption,
        '#credit' => $item->credit,
        '#cta_title' => $cta['title'],
        '#cta_uri' => $cta['uri'],
      ];
    }
    $elements['#attached']['library'][] = 'utexas_hero/hero-style-4';
    return $elements;
  }

}
