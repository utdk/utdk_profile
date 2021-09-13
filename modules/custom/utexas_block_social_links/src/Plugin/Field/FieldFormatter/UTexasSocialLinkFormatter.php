<?php

namespace Drupal\utexas_block_social_links\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\utexas_block_social_links\Services\UTexasSocialLinkOptions;

/**
 * Plugin implementation of the 'utexas_social_link_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_social_link_formatter",
 *   label = @Translation("UTexas Social Link"),
 *   field_types = {
 *     "utexas_social_link_field"
 *   }
 * )
 */
class UTexasSocialLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $icons = UTexasSocialLinkOptions::getIcons();
    foreach ($items as $delta => $item) {
      if ($item->social_account_links) {
        $social_account_links = unserialize($item->social_account_links);
        foreach ($social_account_links as $key => $val) {
          if (!file_exists($icons[$val['social_account_name']])) {
            continue;
          }
          if (!empty($icons[$val['social_account_name']]) && $icon = file_get_contents($icons[$val['social_account_name']])) {
            $icon_markup = Markup::create($icon);
            $linked_icon_options = [
              'attributes' => [
                'class' => [
                  'block__ut-social-links--link',
                ],
                'aria-label' => 'Find us on ' . ucfirst($val['social_account_name']),
              ],
            ];
            $linked_icon = Link::fromTextAndUrl($icon_markup, Url::fromUri($val['social_account_url'], $linked_icon_options));
            $renderable = $linked_icon->toRenderable();
            $elements[$delta]['links'][$key] = $renderable;
          }
        }
      }

      // Add class to the item.attributes object.
      $elements['#items'][$delta] = new \stdClass();
      $elements['#items'][$delta]->_attributes['class'][] = 'block__ut-social-links--item';

      if ($item->headline) {
        $elements[$delta]['headline'] = [
          '#markup' => $item->headline,
        ];
      }
      // Default to small for backwards compatibility.
      $icon_size = $item->icon_size ?? 'ut-social-links--small';
      $elements[$delta]['icon_size'] = [
        '#markup' => $icon_size,
      ];
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);
    $elements['#cache']['tags'][] = 'utexas_social_links_block';
    return $elements;
  }

}
