<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\FeaturedHighlightTestTrait;
use Drupal\Tests\utexas\Traits\FlexContentAreaTestTrait;
use Drupal\Tests\utexas\Traits\HeroImageTestTrait;
use Drupal\Tests\utexas\Traits\PhotoContentAreaTestTrait;
use Drupal\Tests\utexas\Traits\PromoListTestTrait;
use Drupal\Tests\utexas\Traits\QuickLinksTestTrait;
use Drupal\Tests\utexas\Traits\ResourcesTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Verifies Flex Content Area A & B field schema & validation.
 *
 * @group utexas
 */
class CustomWidgetsTest extends BrowserTestBase {

  use EntityTestTrait;
  use ImageFieldCreationTrait;
  use InstallTestTrait;
  use FeaturedHighlightTestTrait;
  use FlexContentAreaTestTrait;
  use HeroImageTestTrait;
  use PhotoContentAreaTestTrait;
  use PromoListTestTrait;
  use QuickLinksTestTrait;
  use ResourcesTestTrait;
  use TestFileCreationTrait;
  use UserTestTrait;

  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

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
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeFlexPageEditor();
    $this->drupalLogin($this->testUser);
    $this->testImage = $this->createTestImage();
    // Generate a test node for referencing an internal link.
    $basic_page_id = $this->createBasicPage();
  }

  /**
   * Test any custom widgets sequentially, using the same installation.
   */
  public function testCustomWidgets() {
    $this->verifyFeaturedHighlight();
    $this->verifyFlexContentArea();
    $this->verifyHeroImage();
    $this->verifyPhotoContentArea();
    $this->verifyPromoList();
    $this->verifyQuickLinks();
    $this->verifyResources();
  }

}
