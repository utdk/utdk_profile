<?php

namespace Drupal\utexas_block_required_links\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'RequiredLinksBlock' block.
 *
 * @Block(
 *  id = "required_links_block",
 *  category = @Translation("UTexas"),
 *  admin_label = @Translation("Required Links Block"),
 * )
 */
class RequiredLinksBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $required_links = [
      'http://www.utexas.edu' => 'UT Austin Home',
      'http://www.utexas.edu/emergency' => 'Emergency Information',
      'http://www.utexas.edu/site-policies' => 'Site Policies',
      'http://www.utexas.edu/web-accessibility-policy' => 'Web Accessibility Policy',
      'http://www.utexas.edu/web-privacy-policy' => 'Web Privacy Policy',
      'https://get.adobe.com/reader/' => 'Adobe Reader',
    ];
    $output = '<ul class="ut-menu--list required-links__list">';
    foreach ($required_links as $url => $title) {
      $output .= '<li class="required-links__list-item"><a href="' . $url . '" class="required-links__link">' . $title . '</a></li>';
    }
    $output .= '</ul>';
    $build = [];
    $build['required_links_block']['#markup'] = $output;

    return $build;
  }

}
