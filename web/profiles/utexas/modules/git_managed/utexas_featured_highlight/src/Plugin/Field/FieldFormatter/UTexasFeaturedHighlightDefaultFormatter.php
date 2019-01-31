<?php

namespace Drupal\utexas_featured_highlight\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Language\Language;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\date_ap_style\ApStyleDateFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'utexas_featured_highlight' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_featured_highlight",
 *   label = @Translation("Limestone (Light)"),
 *   field_types = {
 *     "utexas_featured_highlight"
 *   }
 * )
 */
class UTexasFeaturedHighlightDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\date_ap_style\ApStyleDateFormatter
   */
  protected $apStyleDateFormatter;

  /**
   * Constructs a TimestampAgoFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\date_ap_style\ApStyleDateFormatter $date_formatter
   *   The date formatter.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ApStyleDateFormatter $date_formatter) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->apStyleDateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // @see \Drupal\Core\Field\FormatterPluginManager::createInstance().
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('date_ap_style.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $responsive_image_style_name = 'utexas_responsive_image_fh';
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

      if (isset($item->date)) {
        $options = [
          'always_display_year' => 1,
          'display_noon_and_midnight' => 1,
          'timezone' => '',
          'display_day' => 0,
          'display_time' => 0,
          'time_before_date' => 0,
          'use_all_day' => 0,
          'capitalize_noon_and_midnight' => 0,
        ];
        $timezone = \Drupal::config('system.date')->get('timezone');
        $item->date = $this->apStyleDateFormatter->formatTimestamp(strtotime($item->date), $options, $timezone['default'], Language::LANGCODE_NOT_SPECIFIED);
      }
      $headline = $item->headline ?? '';
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
              'ut-btn',
            ],
          ],
        ];
        $url->setOptions($link_options);
        $cta = Link::fromTextAndUrl($item->link_text, $url);
      }
      $image_render_array = [];
      if ($media = \Drupal::entityTypeManager()->getStorage('media')->load($item->media)) {
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
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

        // Add the file entity to the cache dependencies.
        // This will clear our cache when this entity updates.
        $renderer = \Drupal::service('renderer');
        $renderer->addCacheableDependency($image_render_array, $file);
      }
      $elements[] = [
        '#theme' => 'utexas_featured_highlight',
        '#headline' => $headline,
        '#media' => $image_render_array,
        '#copy' => check_markup($item->copy_value, $item->copy_format),
        '#date' => $item->date,
        '#cta' => $cta ?? '',
        '#style' => '',
      ];
    }
    return $elements;
  }

}
