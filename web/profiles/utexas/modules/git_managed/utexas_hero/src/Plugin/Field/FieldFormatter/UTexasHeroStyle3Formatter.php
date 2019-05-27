<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_hero_3",
 *   label = @Translation("Style 3: White bottom pane with heading, subheading and burnt orange call to action, image anchored center"),
 *   field_types = {
 *     "utexas_hero"
 *   }
 * )
 */
class UTexasHeroStyle3Formatter extends UTexasHeroFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $name = 'hero-style-3';
    $cache_tags = [];
    $elements = [];
    $large_image_style_name = 'utexas_image_style_2250w_900h';
    $medium_image_style_name = 'utexas_image_style_900w';
    $small_image_style_name = 'utexas_image_style_600w';

    // First load image styles & store their style in the cache for this page.
    $large_image_style = $this->entityTypeManager->getStorage('image_style')->load($large_image_style_name);
    $cache_tags = Cache::mergeTags($cache_tags, $large_image_style->getCacheTags());

    $medium_image_style = $this->entityTypeManager->getStorage('image_style')->load($medium_image_style_name);
    $cache_tags = Cache::mergeTags($cache_tags, $medium_image_style->getCacheTags());

    $small_image_style = $this->entityTypeManager->getStorage('image_style')->load($small_image_style_name);
    $cache_tags = Cache::mergeTags($cache_tags, $small_image_style->getCacheTags());

    foreach ($items as $delta => $item) {
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
      $id = Html::getUniqueId($name);
      $background_image = new Attribute();
      if ($media = $this->entityTypeManager->getStorage('media')->load($item->media)) {
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
        if ($file = $this->entityTypeManager->getStorage('file')->load($media_attributes[0]['target_id'])) {
          $uri = $file->getFileUri();
          // Check if image styles have been disabled (e.g., animated GIF)
          if (!$item->disable_image_styles) {
            // Apply an image style in an attempt to optimize huge images.
            $large_src = $large_image_style->buildUrl($uri);
            $medium_src = $medium_image_style->buildUrl($uri);
            $small_src = $small_image_style->buildUrl($uri);
          }
          else {
            $large_src = $file->toUrl();
            $medium_src = $file->toUrl();
            $small_src = $file->toUrl();
          }
          $css = "
          #" . $id . ".ut-hero {
            background-image: url(" . $large_src . ");
          }
          @media screen and (max-width: 900px) { 
            #" . $id . ".ut-hero {
              background-image: url(" . $medium_src . ");
            }
          }
          @media screen and (max-width: 600px) {
            #" . $id . ".ut-hero {
              background-image: url(" . $small_src . ");
            }
          }";
          $elements['#attached']['html_head'][] = [
            [
              '#tag' => 'style',
              '#value' => $css,
            ],
            'utexas-hero-' . $id,
          ];
        }
      }
      $elements[$delta] = [
        '#theme' => 'utexas_hero_3',
        '#media_identifier' => $id,
        '#alt' => isset($media_attributes) ? $media_attributes[0]['alt'] : '',
        '#heading' => $item->heading,
        '#subheading' => $item->subheading,
        '#cta_title' => $cta['title'],
        '#cta_uri' => $cta['uri'],
        '#anchor_position' => 'center',
      ];
    }
    $elements['#attached']['library'][] = 'utexas_hero/hero-style-3';
    return $elements;
  }

}
