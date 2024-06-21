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
      'https://www.utexas.edu' => 'UT Austin Home',
      'https://emergency.utexas.edu' => 'Emergency Information',
      'https://www.utexas.edu/site-policies' => 'Site Policies',
      'https://www.utexas.edu/web-accessibility-policy' => 'Web Accessibility Policy',
      'https://www.utexas.edu/web-privacy-policy' => 'Web Privacy Policy',
      'https://get.adobe.com/reader/' => 'Adobe Reader',
    ];
    $output = '<ul class="ut-menu--list required-links__list">';
    foreach ($required_links as $url => $title) {
      $output .= '<li class="required-links__list-item"><a href="' . $url . '" class="required-links__link ut-cta-link--external">' . $title . '</a></li>';
    }
    $output .= '</ul>';
    $build = [];
    $build['required_links_block']['#markup'] = $output;

    return $build;
  }

}
