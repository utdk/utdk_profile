<?php

namespace Drupal\utexas\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Provides SMTP configuration overrides from Pantheon organization secrets.
 */
class SmtpConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    if (!in_array('smtp.settings', $names)) {
      return $overrides;
    }
    // We allow static calls to services.
    // phpcs:ignore
    if (!\Drupal::state()->get('utexas_smtp')) {
      return $overrides;
    }
    if (!function_exists('pantheon_get_secret')) {
      return $overrides;
    }
    $overrides['smtp.settings']['smtp_on'] = 1;
    $overrides['smtp.settings']['smtp_autotls'] = 1;
    $overrides['smtp.settings']['smtp_host'] = pantheon_get_secret('utexas_smtp_host') ?? NULL;
    $overrides['smtp.settings']['smtp_port'] = pantheon_get_secret('utexas_smtp_port') ?? NULL;
    $overrides['smtp.settings']['smtp_protocol'] = pantheon_get_secret('utexas_smtp_protocol') ?? NULL;
    $overrides['smtp.settings']['smtp_username'] = pantheon_get_secret('utexas_smtp_username') ?? NULL;
    $overrides['smtp.settings']['smtp_password'] = pantheon_get_secret('utexas_smtp_password') ?? NULL;
    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'SmtpConfigOverride';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
