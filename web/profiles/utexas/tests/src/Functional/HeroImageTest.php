<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Hero Image field schema & validation.
 *
 * @group utexas
 */
class HeroImageTest extends BrowserTestBase {
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
    $this->testImage = $this->createTestImage();
  }

  /**
   * Test schema.
   */
  public function testSchema() {
    $assert = $this->assertSession();
    // Verify a user has access to the content type.
    $this->assertAllowed("/node/add/utexas_flex_page");
    // Add the Hero Image paragraph types.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-hi-add-more-add-more-button-utexas-hero-image')->click();

    // Verify the correct field schemae exist.
    $fields = [
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-image-0-upload',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-caption-0-value',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-photo-credit-0-value',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Test validation.
   */
  public function testValidation() {
    $this->assertAllowed("/node/add/utexas_flex_page");
    // Add the Hero Image paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-hi-add-more-add-more-button-utexas-hero-image')->click();
    // Submit an image with no alt text.
    $edit = [
      'title[0][value]' => 'Hero Image Test',
      'files[field_flex_page_hi_0_subform_field_utexas_hi_image_0]' => drupal_realpath($this->testImage),
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    // Images must have alt text.
    $this->assertRaw('Alternative text field is required.');
  }

  /**
   * Test output.
   */
  public function testOutput() {
    $this->assertAllowed("/node/add/utexas_flex_page");
    // Add the Hero Image paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-hi-add-more-add-more-button-utexas-hero-image')->click();
    $edit = [
      'title[0][value]' => 'Hero Image Test',
      'files[field_flex_page_hi_0_subform_field_utexas_hi_image_0]' => drupal_realpath($this->testImage),
      'field_flex_page_hi[0][subform][field_utexas_hi_caption][0][value]' => 'This is a caption',
      'field_flex_page_hi[0][subform][field_utexas_hi_photo_credit][0][value]' => 'This is a photo credit',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_hi[0][subform][field_utexas_hi_image][0][alt]' => 'Alternative text',
    ],
    'edit-submit');
    $node = $this->drupalGetNodeByTitle('Hero Image Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertRaw('utexas_image_style_2280w_1232h/public/hero_images/image-test.png');
    $this->assertRaw('alt="Alternative text"');
    $this->assertRaw('This is a caption');
    $this->assertRaw('This is a photo credit');
    // Sign out!
    $this->drupalLogout();
  }

}
