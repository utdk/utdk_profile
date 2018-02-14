<?php

namespace Drupal\utexas_block_social_links\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

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
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $elements = array();

    foreach ($items as $delta => $item) {
      // The following is placeholder output that will be replaced.
      // Two elements are available for retrieval, the $item->url,
      // which is the user-supplied external URL to a social media site,
      // and the $item->icon, which is a key, such as "facebook" or "twitter,"
      // That can subsequently be used to retrieve an SVG stored in
      // configuration that has the matching key.
      if ($item->icon) {
        $markup = '<h3>' . $item->icon . '</h3>';
      }
      if ($item->url) {
        $markup .= $item->url;
      }

      $elements[$delta] = array(
        '#type' => 'markup',
        '#markup' => $markup,
      );
    }

    return $elements;
  }

}
