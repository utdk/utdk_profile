<?php

namespace Drupal\utexas_flex_content_area\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'utexas_flex_content_area' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_flex_content_area",
 *   label = @Translation("Display 2 items per row."),
 *   field_types = {
 *     "utexas_flex_content_area"
 *   }
 * )
 */
class UTexasFlexContentAreaDefaultFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a FormatterBase object.
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $responsive_image_style_name = 'utexas_responsive_image_fca';
    // Collect cache tags to be added for each item in the field.
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
    foreach ($items as $delta => $item) {
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
      $cta_uri = "";
      $cta = "";
      if (!empty($item->link_uri)) {
        $url = Url::fromUri($item->link_uri);
        $cta_uri = $url->toString();
        // If CTA present wrap headline in its URL.
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
      if ($media = $this->entityTypeManager->getStorage('media')->load($item->image)) {
        // Format image.
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
        $image_render_array = [];
        if ($file = $this->entityTypeManager->getStorage('file')->load($media_attributes[0]['target_id'])) {
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
            '#url' => $cta_uri ?? '',
          ];
        }
        // Add the file entity to the cache dependencies.
        // This will clear our cache when this entity updates.
        $this->renderer->addCacheableDependency($image_render_array, $file);
      }
      else {
        $image_render_array = [];
      }
      $elements[] = [
        '#theme' => 'utexas_flex_content_area',
        '#image' => $image_render_array,
        '#headline' => $headline,
        '#copy' => check_markup($item->copy_value, $item->copy_format),
        '#links' => $links,
        '#cta' => $cta ?? '',
      ];
      $elements['#items'][$delta] = new \stdClass();
      $elements['#items'][$delta]->_attributes = [
        'class' => ['ut-flex-content-area', 'two-col'],
      ];
      $elements['#attributes']['class'][] = 'ut-flex-content-area-wrapper';
    }
    $elements['#attached']['library'][] = 'utexas_flex_content_area/flex-content-area';
    return $elements;

  }

}
