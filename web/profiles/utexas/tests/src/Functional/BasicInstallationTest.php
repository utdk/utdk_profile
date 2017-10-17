<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Ensures that tests for the UTexas installation profile can run.
 *
 * @group utexas
 */
class BasicInstallationTest extends BrowserTestBase {

  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

  /**
   * Tests routes info.
   */
  public function testBootstrap() {
    $this->assertTrue(1 === 1);
  }

}
