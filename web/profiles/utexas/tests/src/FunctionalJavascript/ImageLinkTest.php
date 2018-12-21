<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Image Link field schema & validation.
 *
 * @group utexas
 */
class ImageLinkTest extends WebDriverTestBase {
  use EntityTestTrait;
  use UserTestTrait;
  use ImageFieldCreationTrait;
  use TestFileCreationTrait;
  use InstallTestTrait;
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
   * Test schema.
   */
  public function testImageLink() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('node/add/utexas_flex_page');

    // Verify that both media widget instances are present.
    $assert->pageTextContains('Image Link A');
    $image_link_a_wrapper = $assert->elementExists('css', '#edit-field-flex-page-il-a-base');
    $image_link_a_wrapper->click();
    $image_link_a_button = $assert->elementExists('css', '#edit-field-flex-page-il-a-0-image-media-library-open-button');
    $image_link_a_button->click();
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Media library');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonpane')->pressButton('Select media');
    $assert->assertWaitOnAjaxRequest();

    $assert->pageTextContains('Image Link B');
    $image_link_a_wrapper = $assert->elementExists('css', '#edit-field-flex-page-il-b-base');
    $image_link_a_wrapper->click();
    $image_link_a_button = $assert->elementExists('css', '#edit-field-flex-page-il-b-0-image-media-library-open-button');
    $image_link_a_button->click();
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Media library');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonpane')->pressButton('Select media');
    $assert->assertWaitOnAjaxRequest();

    $this->submitForm([
      'title[0][value]' => 'Image Link Test',
      'field_flex_page_il_a[0][link][url]' => 'https://imagelink.test',
      'field_flex_page_il_b[0][link][url]' => '/node/1',
    ], 'Save');

    // Verify page output.
    $assert->linkByHrefExists('https://imagelink.test');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_500w/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="https://imagelink.test"] picture img', 'src', $expected_path);
    // Verify Image Link B link is internal.
    $assert->linkByHrefExists('/image-link-test');
    // Sign out!
    $this->drupalLogout();
  }

}
