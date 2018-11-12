<?php

namespace Drupal\utexas_quick_links\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'utexas_quick_links' formatter.
 *
 * @FieldFormatter(
 *   id = "utexas_quick_links",
 *   label = @Translation("Display Links in 1 column."),
 *   field_types = {
 *     "utexas_quick_links"
 *   },
 *   weight = 1,
 * )
 */
class UTexasQuickLinksDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $links = unserialize($item->links);
      // Ensure that links without title text print the URL.
      if (!empty($links)) {
        foreach ($links as &$link) {
          if (empty($link['title'])) {
            $url = Url::fromUri($link['url']);
            $url->setAbsolute();
            $link['title'] = $url->toString();
          }
        }
      }
      else {
        $links = [];
      }
      $elements[] = [
        '#theme' => 'utexas_quick_links',
        '#headline' => $item->headline,
        '#copy' => check_markup($item->copy_value, $item->copy_format),
        '#links' => $links,
        '#columns' => 'one',
      ];
      $elements['#attached']['library'][] = 'utexas_quick_links/quick-links';
    }
    return $elements;
  }

}
