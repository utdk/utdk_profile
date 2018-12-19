<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
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
class ImageLinkTest extends BrowserTestBase {
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
  public function testSchema() {
    $assert = $this->assertSession();
    // 1. Verify a user has access to the content type.
    $this->drupalGet("/node/add/utexas_flex_page");
    // 2. Verify the correct field schemae exist.
    $fields = [
      'edit-field-flex-page-il-a-0-image-upload',
      'edit-field-flex-page-il-a-0-link-url',
      'edit-field-flex-page-il-b-0-image-upload',
      'edit-field-flex-page-il-b-0-link-url',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Test validation.
   */
  public function testValidation() {
    $this->drupalGet("/node/add/utexas_flex_page");
    // Submit an image with no alt text.
    $edit = [
      'title[0][value]' => 'Flex Page Test',
      'field_flex_page_il_a[0][image][media_library_selection]' => $this->testImage,
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
  }

  /**
   * Test output.
   */
  public function testOutput() {
    // Generate a test node for referencing an internal link.
    $basic_page_id = $this->createBasicPage();
    $this->drupalGet("/node/add/utexas_flex_page");
    $edit = [
      'title[0][value]' => 'Image Link Test',
      'field_flex_page_il_a[0][image][media_library_selection]' => $this->testImage,
      'edit-field-flex-page-il-a-0-link-url' => 'https://markfullmer.com',
      'field_flex_page_il_a[0][image][media_library_selection]' => $this->testImage,
      'edit-field-flex-page-il-b-0-link-url' => '/node/' . $basic_page_id,
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    $node = $this->drupalGetNodeByTitle('Image Link Test');
    $this->drupalGet('node/' . $node->id());
    // 3. Verify Image Link A is present, and that the link is external.
    $this->assertRaw('utexas_image_style_1800w/public/image_link/image-test.png');
    $this->assertRaw('<a href="https://markfullmer.com"');
    // 4. Verify Image Link B is present, and that the link is internal.
    $this->assertRaw('utexas_image_style_1800w/public/image_link/image-test_0.png');
    $this->assertRaw('<a href="/test-basic-page');
    /* Verify a picture tag and srcset are in the markup to show the image is
    rendered as a responsive image. */
    $this->assertRaw('<picture>');
    $this->assertRaw('<source srcset');
    // Sign out!
    $this->drupalLogout();
  }

}
