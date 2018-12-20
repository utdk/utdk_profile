<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Photo Content Area field schema, validation, & output.
 *
 * @group utexas
 */
class PhotoContentAreaTest extends BrowserTestBase {
  use EntityTestTrait;
  use ImageFieldCreationTrait;
  use InstallTestTrait;
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
    $this->testImage = $this->createTestImage();
  }

  /**
   * Test schema.
   */
  public function testSchema() {
    $assert = $this->assertSession();
    // 1. Verify a user has access to the content type.
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 2. Add the Photo Content Area paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pca-add-more-add-more-button-utexas-photo-content-area')->click();
    // 3. Verify the correct field schemae exist.
    $fields = [
      'edit-field-flex-page-pca-0-subform-field-utexas-pca-image-0-upload',
      'edit-field-flex-page-pca-0-subform-field-utexas-pca-credit-0-value',
      'edit-field-flex-page-pca-0-subform-field-utexas-pca-headline-0-value',
      'edit-field-flex-page-pca-0-subform-field-utexas-pca-copy-0-value',
      'edit-field-flex-page-pca-0-subform-field-utexas-pca-links-0-uri',
      'edit-field-flex-page-pca-0-subform-field-utexas-pca-links-0-title',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Validation: Photo Content Area image is required & alt text is required.
   */
  public function testValidation() {
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 2. Add the Photo Content Area paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pca-add-more-add-more-button-utexas-photo-content-area')->click();
    // 2. Submit a Photo Content Area with no photo.
    $edit = [
      'title[0][value]' => 'Photo Content Area Test',
      'field_flex_page_pca[0][subform][field_utexas_pca_headline][0][value]' => 'Headline',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $this->assertRaw('Image field is required.');
    $this->drupalPostForm(NULL, [
      'files[field_flex_page_pca_0_subform_field_utexas_pca_image_0]' => drupal_realpath($this->testImage),
    ],
      'edit-submit');
    $this->assertRaw('Alternative text field is required.');
  }

  /**
   * Test output.
   */
  public function testOutput() {
    // Generate a test node for referencing an internal link.
    $basic_page_id = $this->createBasicPage();
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 1. Add the Photo Content Area paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pca-add-more-add-more-button-utexas-photo-content-area')->click();
    $edit = [
      'title[0][value]' => 'Photo Content Area Test',
      'files[field_flex_page_pca_0_subform_field_utexas_pca_image_0]' => drupal_realpath($this->testImage),
      'field_flex_page_pca[0][subform][field_utexas_pca_headline][0][value]' => 'Headline',
      'field_flex_page_pca[0][subform][field_utexas_pca_copy][0][value]' => 'Copy Value',
      'field_flex_page_pca[0][subform][field_utexas_pca_links][0][title]' => 'External Link',
      'field_flex_page_pca[0][subform][field_utexas_pca_links][0][uri]' => 'https://example.com',
    ];
    // 2. Submit the form now to trigger the the alt text field.
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    // 3. Set alt text.
    $this->drupalPostForm(NULL, [
      'field_flex_page_pca[0][subform][field_utexas_pca_image][0][alt]' => 'Alt text',
    ],
      'edit-submit');
    $node = $this->drupalGetNodeByTitle('Photo Content Area Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    // 4. Verify headline, is present.
    $this->assertRaw('Headline');
    // 5. Verify link, delta 0, is present, and is an external link.
    $this->assertRaw('Copy Value');
    $this->assertRaw('<a href="https://example.com" class="ut-link">External Link</a>');
    // 6. Verify an image is present.
    $picture_tag = $this->getSession()->getPage()->find('css', 'picture')->getHtml();
    $this->assertTrue(strpos($picture_tag, 'photo_content_area/image-test'));

    // Edit the node to add a second photo content area link.
    $this->drupalGet('node/' . $node->id() . '/edit');

    $this->drupalPostForm(NULL, [
      'field_flex_page_pca[0][subform][field_utexas_pca_links][1][title]' => 'Internal Link',
      'field_flex_page_pca[0][subform][field_utexas_pca_links][1][uri]' => '/node/' . $basic_page_id,
    ],
      'edit-submit');

    $this->drupalGet('node/' . $node->id());
    // 7. Verify link, delta 1, is present, and is an internal link.
    $this->assertRaw('<a href="/test-basic-page" class="ut-link">Internal Link</a>');

    // Sign out!
    $this->drupalLogout();
  }

}
