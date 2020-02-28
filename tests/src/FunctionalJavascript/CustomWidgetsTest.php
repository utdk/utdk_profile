<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\FlexContentAreaTestTrait;
use Drupal\Tests\utexas\Traits\FeaturedHighlightTestTrait;
use Drupal\Tests\utexas\Traits\HeroTestTrait;
use Drupal\Tests\utexas\Traits\ImageLinkTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\PhotoContentAreaTestTrait;
use Drupal\Tests\utexas\Traits\PromoListTestTrait;
use Drupal\Tests\utexas\Traits\PromoUnitTestTrait;
use Drupal\Tests\utexas\Traits\QuickLinksTestTrait;
use Drupal\Tests\utexas\Traits\ResourcesTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\LayoutBuilderIntegrationTestTrait;

/**
 * Verifies custom compound field schema, validation, & output.
 *
 * @group utexas
 */
class CustomWidgetsTest extends WebDriverTestBase {
  use EntityTestTrait;
  use FlexContentAreaTestTrait;
  use FeaturedHighlightTestTrait;
  use HeroTestTrait;
  use ImageFieldCreationTrait;
  use ImageLinkTestTrait;
  use InstallTestTrait;
  use PhotoContentAreaTestTrait;
  use PromoListTestTrait;
  use PromoUnitTestTrait;
  use QuickLinksTestTrait;
  use ResourcesTestTrait;
  use TestFileCreationTrait;
  use UserTestTrait;
  use LayoutBuilderIntegrationTestTrait;

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
    $this->testImage = $this->createTestMediaImage();
    $this->testVideo = $this->createTestMediaVideoExternal();
  }

  /**
   * Test any custom widgets sequentially, using the same installation.
   */
  public function testCustomWidgets() {
    $this->getSession()->getPage();
    $this->getSession()->resizeWindow(900, 2000);
    $this->verifyNoDuplicateMenuBlocks();
    $this->verifyPromoList();
    $this->verifyResources();
    $this->verifyImageLink();
    $this->verifyQuickLinks();
    $this->verifyFeaturedHighlight();
    $this->verifyHero();
    $this->verifyFlexContentArea();
    $this->verifyPhotoContentArea();
    $this->verifyPromoUnit();
  }

}
