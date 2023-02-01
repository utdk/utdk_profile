<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;

use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Base class for Functional tests.
 */
abstract class FunctionalTestBase extends BrowserTestBase {

  use EntityTestTrait;
  use InstallTestTrait;
  use UserTestTrait;

  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

  /**
   * Specify the theme to be used in testing.
   *
   * @var string
   */
  protected $defaultTheme = 'forty_acres';

  /**
   * A user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testSiteManagerUser;

  /**
   * A user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testContentEditorUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->utexasSharedSetup();
    parent::setUp();

    $this->testContentEditorUser = $this->initializeContentEditor();
    $this->testSiteManagerUser = $this->initializeSiteManager();
  }

  /**
   * Asserts that the current user can access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  protected function assertAllowed($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Asserts that the current user cannot access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  protected function assertForbidden($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);
  }

}
