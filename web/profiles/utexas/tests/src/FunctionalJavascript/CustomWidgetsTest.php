<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\FeaturedHighlightTestTrait;
use Drupal\Tests\utexas\Traits\HeroTestTrait;
use Drupal\Tests\utexas\Traits\ImageLinkTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\PromoListTestTrait;
use Drupal\Tests\utexas\Traits\PromoUnitTestTrait;
use Drupal\Tests\utexas\Traits\ResourcesTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Verifies custom compound field schema, validation, & output.
 *
 * @group utexas
 */
class CustomWidgetsTest extends WebDriverTestBase {
  use EntityTestTrait;
  use FeaturedHighlightTestTrait;
  use HeroTestTrait;
  use ImageFieldCreationTrait;
  use ImageLinkTestTrait;
  use InstallTestTrait;
  use PromoListTestTrait;
  use PromoUnitTestTrait;
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
    $this->testImage = $this->createTestMediaImage();
  }

  /**
   * Test any custom widgets sequentially, using the same installation.
   */
  public function testCustomWidgets() {
    $this->verifyHero();
    $this->verifyResources();
    $this->verifyFeaturedHighlight();
    $this->verifyImageLink();
    $this->verifyPromoUnit();
    $this->verifyPromoList();
  }

}
