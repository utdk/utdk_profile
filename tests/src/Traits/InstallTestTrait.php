<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * General-purpose methods for setup & installation.
 */
trait InstallTestTrait {

  /**
   * {@inheritdoc}
   */
  public function utexasSharedSetup() {
    $this->strictConfigSchema = NULL;
  }

}
