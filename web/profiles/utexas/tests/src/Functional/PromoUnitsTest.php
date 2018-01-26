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
class PromoUnitsTest extends BrowserTestBase {
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
    // 1. Verify a user has access to the content type.
    $this->assertAllowed("/node/add/utexas_flex_page");

    // 2. Add a Promo Unit paragraph type instance.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pu-add-more-add-more-button-utexas-promo-unit-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pu-0-subform-field-utexas-puc-items-add-more-add-more-button-utexas-promo-unit')->click();

    // 3. Verify the correct field schema exist.
    $fields = [
      'edit-field-flex-page-pu-0-subform-field-utexas-puc-title-0-value',
      'edit-field-flex-page-pu-0-subform-field-utexas-puc-items-0-subform-field-utexas-pu-image-0-upload',
      'edit-field-flex-page-pu-0-subform-field-utexas-puc-items-0-subform-field-utexas-pu-image-style',
      'edit-field-flex-page-pu-0-subform-field-utexas-puc-items-0-subform-field-utexas-pu-headline-0-value',
      'edit-field-flex-page-pu-0-subform-field-utexas-puc-items-0-subform-field-utexas-pu-copy-0-value',
      'edit-field-flex-page-pu-0-subform-field-utexas-puc-items-0-subform-field-utexas-pu-cta-link-0-uri',
      'edit-field-flex-page-pu-0-subform-field-utexas-puc-items-0-subform-field-utexas-pu-cta-link-0-title',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Test validation.
   */
  public function testValidation() {
    // Currently, no fields in Promo Unit are required,
    // therefore, there is no validation to test.
  }

  /**
   * Test output.
   */
  public function testOutput() {
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 1. Add the Promo Unit paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pu-add-more-add-more-button-utexas-promo-unit-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pu-0-subform-field-utexas-puc-items-add-more-add-more-button-utexas-promo-unit')->click();
    $edit = [
      'title[0][value]' => 'Promo Unit Test',
      'field_flex_page_pu[0][subform][field_utexas_puc_title][0][value]' => "Test Title of Promo Unit Container",
      'files[field_flex_page_pu_0_subform_field_utexas_puc_items_0_subform_field_utexas_pu_image_0]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_pu[0][subform][field_utexas_puc_items][0][subform][field_utexas_pu_image_style]' => 'utexas_responsive_image_promo_unit_square',
      'field_flex_page_pu[0][subform][field_utexas_puc_items][0][subform][field_utexas_pu_headline][0][value]' => 'Promo Unit Headline',
      'field_flex_page_pu[0][subform][field_utexas_puc_items][0][subform][field_utexas_pu_copy][0][value]' => 'Promo Unit Copy',
      'field_flex_page_pu[0][subform][field_utexas_puc_items][0][subform][field_utexas_pu_cta_link][0][uri]' => 'https://www.tylerfahey.com',
      'field_flex_page_pu[0][subform][field_utexas_puc_items][0][subform][field_utexas_pu_cta_link][0][title]' => 'Test Promo Unit Link',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // 2. Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_pu[0][subform][field_utexas_puc_items][0][subform][field_utexas_pu_image][0][alt]' => 'Alt A',
    ],
      'edit-submit');
    $node = $this->drupalGetNodeByTitle('Promo Unit Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    // 3. Verify Promo Unit data is present, and that the link is external.
    $this->assertRaw('Test Title of Promo Unit Container');
    $this->assertRaw('utexas_image_style_112w_112h/public/promo_units/image-test.png');
    $this->assertRaw('Promo Unit Headline');
    $this->assertRaw('Promo Unit Copy');
    $this->assertRaw('Test Promo Unit Link');
    $this->assertRaw('<a href="https://www.tylerfahey.com"');
    $this->assertRaw('alt="Alt A"');

    // Test for landscape image based on user selection.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pu-0-subform-field-utexas-puc-items-0-top-links-edit-button')->click();
    $edit = [
      'field_flex_page_pu[0][subform][field_utexas_puc_items][0][subform][field_utexas_pu_image_style]' => 'utexas_responsive_image_promo_unit_landscape',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('utexas_image_style_176w_112h/public/promo_units/image-test.png');

    // Test for portrait image based on user selection.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pu-0-subform-field-utexas-puc-items-0-top-links-edit-button')->click();
    $edit = [
      'field_flex_page_pu[0][subform][field_utexas_puc_items][0][subform][field_utexas_pu_image_style]' => 'utexas_responsive_image_promo_unit_portrait',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('utexas_image_style_120w_150h/public/promo_units/image-test.png');

    // Sign out!
    $this->drupalLogout();
  }

}
