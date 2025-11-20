<?php

namespace Drupal\utexas_hero_carousel\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;

use Drupal\utexas_hero\Plugin\Field\FieldFormatter\UTexasHeroFormatterBase;

/**
 * Plugin implementation of the 'utexas_hero' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_hero_carousel",
 *   label = @Translation("Hero Carousel: full-width image"),
 *   field_types = {
 *     "utexas_hero"
 *   }
 * )
 */
class HeroCarouselDefaultFormatter extends UTexasHeroFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Get id of the parent formatter to use.
    $third_party_settings = $this->getThirdPartySettings('utexas_hero_carousel');
    $parent_formatter_id = $third_party_settings['style'] ?? 'utexas_hero_3';

    // Build configuration array to pass to a new instance of whichever existing
    // formatter we're "extending".
    $configuration['field_definition'] = $this->fieldDefinition;
    $configuration['settings'] = $this->settings;
    $configuration['label'] = $this->label;
    $configuration['view_mode'] = $this->viewMode;
    $configuration['third_party_settings'] = $this->thirdPartySettings;

    /** @var \Drupal\Core\Field\FormatterPluginManager $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.field.formatter');
    /** @var \Drupal\Core\Field\FormatterInterface $parent_plugin_instance **/
    $parent_plugin_instance = $plugin_manager->createInstance($parent_formatter_id, $configuration);
    // Call existing formatter's viewElements() method as a starting place.
    $elements = $parent_plugin_instance->viewElements($items, $langcode);

    // Add unique id.
    $js_data_id = Html::getUniqueId($items->getFieldDefinition()->getName());

    // Set "default" drupalSettings for Slick JS.
    $drupal_settings = [
      'autoplay' => 1,
      'autoplaySpeed' => 5,
      'dots' => 1,
      'fade' => 0,
      'slidesToScroll' => 1,
      'slidesToShow' => 1,
    ];

    $elements['#attributes']['id'] = $js_data_id;
    $elements['#attributes']['class'][] = 'utexas-hero-carousel';
    $elements['#attached']['drupalSettings']['utexas_hero_carousel'][$js_data_id] = $drupal_settings;
    $elements['#attached']['library'][] = 'utexas_hero_carousel/slick-carousel';
    return $elements;
  }

}
