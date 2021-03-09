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
use Drupal\Tests\contextual\FunctionalJavascript\ContextualLinkClickTrait;

/**
 * Verifies custom compound field schema, validation, & output.
 *
 * @group utexas
 */
class WidgetsTest extends WebDriverTestBase {
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
  use ContextualLinkClickTrait;

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
  protected function setUp(): void {
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
    $this->getSession()->resizeWindow(1200, 5000);
    $this->verifyResourceCollectionLinks();
    $this->verifyResources();
    $this->verifyPromoList();
    $this->verifyPromoUnit();
    $this->verifyNoDuplicateMenuBlocks();
    $this->verifyImageLink();
    $this->verifyQuickLinks();
    $this->verifyFeaturedHighlight();
    $this->verifyHero();
    $this->verifyFlexContentArea();
    $this->verifyPhotoContentArea();
  }

}
