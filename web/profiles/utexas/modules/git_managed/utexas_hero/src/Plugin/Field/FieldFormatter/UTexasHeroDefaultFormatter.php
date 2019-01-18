<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_hero",
 *   label = @Translation("Hero (Default)"),
 *   field_types = {
 *     "utexas_hero"
 *   }
 * )
 */
class UTexasHeroDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $responsive_image_style_name = 'utexas_responsive_image_hi';
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
      $cta['title'] = '';
      $cta['uri'] = '';
      if (!empty($item->link_uri)) {
        $url = Url::fromUri($item->link_uri);
        $cta['uri'] = $url->toString();
        if (empty($item->link_title)) {
          $url = Url::fromUri($item->link_uri);
          $url->setAbsolute();
          $cta['title'] = $url->toString();
        }
        else {
          $cta['title'] = $item->link_title;
        }
      }
      if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($item->media)) {
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
        $image_render_array = [];
        if ($file = \Drupal::entityTypeManager()->getStorage('file')->load($media_attributes[0]['target_id'])) {
          $image = new \stdClass();
          $image->title = NULL;
          $image->alt = $media_attributes[0]['alt'];
          $image->entity = $file;
          $image->uri = $file->getFileUri();
          $image->width = NULL;
          $image->height = NULL;
          $image_render_array = [
            '#theme' => 'responsive_image_formatter',
            '#item' => $image,
            '#item_attributes' => [],
            '#responsive_image_style_id' => $responsive_image_style_name,
            '#url' => $link ?? '',
            '#cache' => [
              'tags' => $cache_tags,
            ],
          ];
        }
      }
      $elements[] = [
        '#theme' => 'utexas_hero',
        '#media' => $image_render_array,
        '#heading' => $item->heading,
        '#subheading' => $item->subheading,
        '#caption' => $item->caption,
        '#credit' => $item->credit,
        '#cta_title' => $cta['title'],
        '#cta_uri' => $cta['uri'],
      ];
    }
    return $elements;
  }

}
