<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Promo Unit field schema & validation.
 *
 * @group utexas
 */
class ResourcesTest extends BrowserTestBase {
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

    // Add a Promo Unit paragraph type instance.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-add-more-add-more-button-utexas-resource-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-0-subform-field-utexas-rc-items-add-more-add-more-button-utexas-resource')->click();

    // Verify the correct field schema exist.
    $fields = [
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-title-0-value',
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-subform-field-utexas-resource-image-0-upload',
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-subform-field-utexas-resource-headline-0-value',
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-subform-field-utexas-resource-links-0-uri',
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-subform-field-utexas-resource-links-0-title',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Test validation.
   */
  public function testValidation() {
    // Currently, no fields in Resource are required,
    // therefore, there is no validation to test.
  }

  /**
   * Test output.
   */
  public function testOutput() {
    $basic_page_id = $this->createBasicPage();
    $this->assertAllowed("/node/add/utexas_flex_page");
    // Add the Promo Unit paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-add-more-add-more-button-utexas-resource-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-0-subform-field-utexas-rc-items-add-more-add-more-button-utexas-resource')->click();
    $edit = [
      'title[0][value]' => 'Resource Test',
      'field_flex_page_resource[0][subform][field_utexas_rc_title][0][value]' => "Test Title for Resource Field",
      'files[field_flex_page_resource_0_subform_field_utexas_rc_items_0_subform_field_utexas_resource_image_0]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_headline][0][value]' => 'Resource Headline',
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_links][0][uri]' => 'https://www.google.com',
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_links][0][title]' => 'Resource Link 1',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_image][0][alt]' => 'alternative text',
    ],
      'edit-submit');
    $node = $this->drupalGetNodeByTitle('Resource Test');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // Verify we can add a second link item to Resource instance.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-top-links-edit-button')->click();
    $this->drupalPostForm(NULL, [
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_links][1][title]' => 'Resource Link 2',
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_links][1][uri]' => '/node/' . $basic_page_id,
    ],
      'edit-submit');
    $node = $this->drupalGetNodeByTitle('Resource Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertRaw('Test Title for Resource Field');
    $this->assertRaw('Resource Headline');
    // Test image upload and alt text.
    $this->assertRaw('utexas_image_style_800w_500h/public/resources/image-test.png');
    $this->assertRaw('alt="alternative text"');
    $this->assertRaw('Resource Link 1');
    // Test external link.
    $this->assertRaw('<a href="https://www.google.com"');
    // Test internal link and addition of multiple links.
    $this->assertRaw('<a href="/test-basic-page" class="ut-cta-link--darker">Resource Link 2</a>');

    // Sign out!
    $this->drupalLogout();
  }

}
