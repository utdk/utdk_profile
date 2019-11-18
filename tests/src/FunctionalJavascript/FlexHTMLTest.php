<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\FlexHTMLTestTrait;

/**
 * Verifies Flex HTML behavior.
 *
 * @group utexas
 */
class FlexHTMLTest extends WebDriverTestBase {
  use EntityTestTrait;
  use TestFileCreationTrait;
  use InstallTestTrait;
  use UserTestTrait;
  use FlexHTMLTestTrait;

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
   * An user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testUser;
  /**
   * An image uri to be used with file uploads.
   *
   * @var string
   */
  protected $testImage;

  /**
   * An video Media ID to be used with file rendering.
   *
   * @var string
   */
  protected $testVideo;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeContentEditor();
    $this->drupalLogin($this->testUser);
  }

  /**
   * Test any FlexHTML settings sequentially, using the same installation.
   */
  public function testFlexHtml() {
    $page = $this->getSession()->getPage();
    $this->getSession()->resizeWindow(900, 2000);
    $this->verifyQualtricsFilterOutput();
  }

}
