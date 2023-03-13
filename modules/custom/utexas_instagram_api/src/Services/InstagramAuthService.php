<?php

namespace Drupal\utexas_instagram_api\Services;

use Drupal\Core\Config\ConfigFactory;

/**
 * Class InstagramAuthService.
 *
 * @package Drupal\utexas_instagram_api\Services
 */
class InstagramAuthService {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Getter.
   */
  public function get($name){
    return $this->configFactory->get($name);
  }
}
