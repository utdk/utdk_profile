<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Ensures that tests for the UTexas installation profile can run.
 *
 * @group utexas
 */
class BasicInstallationTest extends BrowserTestBase {
  use InstallTestTrait;
  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->utexasSharedSetup();
    parent::setUp();
  }

  /**
   * Tests routes info.
   */
  public function testBootstrap() {
    $this->assertTrue(1 === 1);
  }

}
