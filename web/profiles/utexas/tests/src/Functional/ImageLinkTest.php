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
    parent::setUp();
    // $this->initializeFlexPageEditor();
    $permissions = [
      "create utexas_flex_page content",
      "edit any utexas_flex_page content",
      "delete any utexas_flex_page content",
    ];
    $this->testUser = $this->drupalCreateUser($permissions);
    $this->drupalLogin($this->testUser);
    $this->testImage = $this->createTestImage();
  }

  /**
   * Test schema.
   */
  public function testSchema() {
    $assert = $this->assertSession();
    // 1. Verify a user has access to the content type.
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 2. Verify the correct field schemae exist.
    $fields = [
      'edit-field-flex-page-il-a-0-subform-field-utexas-il-image-0-upload',
      'edit-field-flex-page-il-a-0-subform-field-utexas-il-link-0-uri',
      'edit-field-flex-page-il-b-0-subform-field-utexas-il-image-0-upload',
      'edit-field-flex-page-il-b-0-subform-field-utexas-il-link-0-uri',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Test validation.
   */
  public function testValidation() {
    // Submit an image with no alt text.
    $edit = [
      'title[0][value]' => 'Flex Page Test',
      'files[field_flex_page_il_a_0_subform_field_utexas_il_image_0]' => drupal_realpath($this->testImage),
    ];
    $this->drupalPostForm("/node/add/utexas_flex_page", $edit, 'edit-submit');
    // Images must have alt text.
    $this->assertRaw('Alternative text field is required.');
  }

  /**
   * Test output.
   */
  public function testOutput() {
    $edit = [
      'title[0][value]' => 'Image Link Test',
      'files[field_flex_page_il_a_0_subform_field_utexas_il_image_0]' => drupal_realpath($this->testImage),
      'field_flex_page_il_a[0][subform][field_utexas_il_link][0][uri]' => 'https://markfullmer.com',
      'files[field_flex_page_il_b_0_subform_field_utexas_il_image_0]' => drupal_realpath($this->testImage),
      'field_flex_page_il_b[0][subform][field_utexas_il_link][0][uri]' => 'https://google.com',
    ];
    $this->drupalPostForm("/node/add/utexas_flex_page", $edit, 'edit-submit');

    // Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_il_a[0][subform][field_utexas_il_image][0][alt]' => 'Alt A',
      'field_flex_page_il_b[0][subform][field_utexas_il_image][0][alt]' => 'Alt B',
    ],
    'edit-submit');
    $node = $this->drupalGetNodeByTitle('Image Link Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    // Verify Image Link A is present.
    global $base_url;
    $url = file_create_url($this->testImage);
    $url = str_replace($base_url . '/', '', $url);
    $this->assertRaw('<img src="/' . $url . '" width="40" height="20" alt="Alt A">');
    $this->assertRaw('<div class="field field--name-field-utexas-il-link field--type-link field--label-hidden field__item">https://markfullmer.com</div>');

    // Sign out!
    $this->drupalLogout();
  }

}
