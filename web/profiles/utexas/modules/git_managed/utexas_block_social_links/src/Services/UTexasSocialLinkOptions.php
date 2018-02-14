<?php

namespace Drupal\utexas_block_social_links\Services;

/**
 * Class UTexasSocialLinkOptions.
 *
 * @package Drupal\utexas_block_social_links
 */
class UTexasSocialLinkOptions {

  /**
   * Provides a key-value array of social link options.
   *
   * This is used in two places: UTexasSocialLinkField::generateSampleValue()
   * and UTexasSocialLinkWidget->formElement().
   *
   * @return array
   *   A render array.
   */
  public static function getOptionsArray() {
    // Currently, this serves this hardcoded array.
    // Subsequently, it will retrieve data from configuration
    // located elsewhere (e.g., a configuration entity).
    $options = [
      'facebook' => t('Facebook'),
      'twitter' => t('Twitter'),
      'instagram' => t('Instagram'),
      'linkedin' => t('LinkedIn'),
      'youtube' => t('YouTube'),
      'googleplus' => t('Google Plus'),
      'flickr' => t('FlickR'),
      'pinterest' => t('Pinterest'),
      'tumblr' => t('Tumblr'),
      'vimeo' => t('Vimeo'),
    ];
    return $options;
  }

}
