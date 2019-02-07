<?php

namespace Drupal\utexas_hero\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'utexas_hero_5' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_hero_5",
 *   label = @Translation("Style 5: Medium image, floated right, with large heading, subheading and burnt orange call-to-action"),
 *   field_types = {
 *     "utexas_hero"
 *   }
 * )
 */
class UTexasHeroStyle5Formatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $name = 'hero-style-5';
    $cache_tags = [];
    $elements = [];
    $large_image_style_name = 'utexas_image_style_2250w_900h';
    $medium_image_style_name = 'utexas_image_style_900w';
    $small_image_style_name = 'utexas_image_style_600w';

    // First load image styles & store their style in the cache for this page.
    $large_image_style = \Drupal::entityTypeManager()->getStorage('image_style')->load($large_image_style_name);
    $cache_tags = Cache::mergeTags($cache_tags, $large_image_style->getCacheTags());

    $medium_image_style = \Drupal::entityTypeManager()->getStorage('image_style')->load($medium_image_style_name);
    $cache_tags = Cache::mergeTags($cache_tags, $medium_image_style->getCacheTags());

    $small_image_style = \Drupal::entityTypeManager()->getStorage('image_style')->load($small_image_style_name);
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
      if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($item->media)) {
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
        if ($file = \Drupal::entityTypeManager()->getStorage('file')->load($media_attributes[0]['target_id'])) {
          $uri = $file->getFileUri();
          // Exclude GIFs from image style to allow for animation.
          if ($file->getMimeType() != 'image/gif') {
            // Apply an image style in an attempt to optimize huge images.
            $large_src = $large_image_style->buildUrl($uri);
            $medium_src = $medium_image_style->buildUrl($uri);
            $small_src = $small_image_style->buildUrl($uri);
          }
          else {
            $large_src = $file->url();
            $medium_src = $file->url();
            $small_src = $file->url();
          }
          $css = "
          .hero--half-n-half #" . $id . ".hero-img {
            background-image: url(" . $large_src . ");
          }
          @media screen and (max-width: 900px) { 
            .hero--half-n-half #" . $id . ".hero-img {
              background-image: url(" . $medium_src . ");
            }
          }
          @media screen and (max-width: 600px) {
            .hero--half-n-half #" . $id . ".hero-img {
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
        '#theme' => 'utexas_hero_5',
        '#media_identifier' => $id,
        '#alt' => isset($media_attributes) ? $media_attributes[0]['alt'] : '',
        '#heading' => $item->heading,
        '#subheading' => $item->subheading,
        '#cta_title' => $cta['title'],
        '#cta_uri' => $cta['uri'],
      ];
    }
    $elements['#attached']['library'][] = 'utexas_hero/hero-style-5';
    return $elements;
  }

}
