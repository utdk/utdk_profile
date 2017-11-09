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
      'edit-field-image-link-0-subform-field-utexas-il-image-0-upload',
      'edit-field-image-link-0-subform-field-utexas-il-link-0-uri',
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
      'files[field_flex_page_il_0_subform_field_utexas_il_image_0]' => drupal_realpath($this->testImage),
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
      'files[field_flex_page_il_0_subform_field_utexas_il_image_0]' => drupal_realpath($this->testImage),
      'field_flex_page_il[0][subform][field_utexas_il_links][0][uri]' => 'https://markfullmer.com',
    ];
    $this->drupalPostForm("/node/add/utexas_flex_page", $edit, 'edit-submit');
    // Verify we can add a second Image Link instance.
    $this->getSession()->getPage()->find('css', '#edit-field-image-link-add-more-add-more-button-utexas-image-link')->click();

    // Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_il[0][subform][field_utexas_il_image][0][alt]' => 'Alt 1',
      'files[field_flex_page_il_1_subform_field_utexas_il_image_0]' => drupal_realpath($this->testImage),
      'field_flex_page_il[0][subform][field_utexas_il_links][1][uri]' => 'https://genderedtextproject.com',
      'field_flex_page_il[0][subform][field_utexas_il_image][1][alt]' => 'Alt 2',
    ],
    'edit-submit');
    $node = $this->drupalGetNodeByTitle('Image Link Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    // Verify Image Link, delta 0, is present.
    $this->assertRaw('<img src="' . drupal_realpath($this->testImage) . '" width="1191" height="670" alt="Alt 1">');
    $this->assertRaw('<div class="field field--name-field-utexas-il-link field--type-link field--label-hidden field__item">https://markfullmer.com</div>');
    // Verify Image Link, delta 1, is present.
    // Sign out!
    $this->drupalLogout();
  }
}
