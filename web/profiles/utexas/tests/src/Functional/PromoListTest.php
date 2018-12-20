<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Promo List field schema & validation.
 *
 * @group utexas
 */
class PromoListTest extends BrowserTestBase {
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

    // 2. Add a Promo List paragraph type instance.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pl-add-more-add-more-button-utexas-promo-list-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pl-0-subform-field-utexas-plc-items-add-more-add-more-button-utexas-promo-list')->click();

    // 3. Verify the correct field schema exist.
    $fields = [
      'edit-field-flex-page-pl-0-subform-field-utexas-plc-headline-0-value',
      'edit-field-flex-page-pl-0-subform-field-utexas-plc-items-0-subform-field-utexas-pl-headline-0-value',
      'edit-field-flex-page-pl-0-subform-field-utexas-plc-items-0-subform-field-utexas-pl-image-0-upload',
      'edit-field-flex-page-pl-0-subform-field-utexas-plc-items-0-subform-field-utexas-pl-copy-0-value',
      'edit-field-flex-page-pl-0-subform-field-utexas-plc-items-0-subform-field-utexas-pl-link-0-uri',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Test validation.
   */
  public function testValidation() {
    // Currently, no fields in Promo List are required,
    // therefore, there is no validation to test.
  }

  /**
   * Test output.
   */
  public function testOutput() {
    // Generate a test node for referencing an internal link.
    $basic_page_id = $this->createBasicPage();
    $this->assertAllowed("/node/add/utexas_flex_page");
    // Add First Promo List Container.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pl-add-more-add-more-button-utexas-promo-list-container')->click();
    // Add the First Promo List Container first Promo List Item.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pl-0-subform-field-utexas-plc-items-add-more-add-more-button-utexas-promo-list')->click();
    // Add the First Promo List Container second Promo List Item.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pl-0-subform-field-utexas-plc-items-add-more-add-more-button-utexas-promo-list')->click();
    // Second Promo List Container.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pl-add-more-add-more-button-utexas-promo-list-container')->click();
    // Add the Second Promo List Container first Promo List Item.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pl-1-subform-field-utexas-plc-items-add-more-add-more-button-utexas-promo-list')->click();
    // Add the Second Promo List Container second Promo List Item.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-pl-1-subform-field-utexas-plc-items-add-more-add-more-button-utexas-promo-list')->click();
    $edit = [
      'title[0][value]' => 'Promo List Test',
      // First Promo List Container.
      'field_flex_page_pl[0][subform][field_utexas_plc_headline][0][value]' => "Test Title of First Promo List Container",
      // First Container - first Promo List item instance with an external link.
      'files[field_flex_page_pl_0_subform_field_utexas_plc_items_0_subform_field_utexas_pl_image_0]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_pl[0][subform][field_utexas_plc_items][0][subform][field_utexas_pl_headline][0][value]' => 'First Container First Item Promo List Headline',
      'field_flex_page_pl[0][subform][field_utexas_plc_items][0][subform][field_utexas_pl_copy][0][value]' => 'First Container First Item Promo List Copy',
      'field_flex_page_pl[0][subform][field_utexas_plc_items][0][subform][field_utexas_pl_link][0][uri]' => 'https://www.tylerfahey.com',
      // First Container - second Promo List item instance with internal link.
      'files[field_flex_page_pl_0_subform_field_utexas_plc_items_1_subform_field_utexas_pl_image_0]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_pl[0][subform][field_utexas_plc_items][1][subform][field_utexas_pl_headline][0][value]' => 'First Container Second Item Promo List Headline',
      'field_flex_page_pl[0][subform][field_utexas_plc_items][1][subform][field_utexas_pl_copy][0][value]' => 'First Container Second Item Promo List Copy',
      'field_flex_page_pl[0][subform][field_utexas_plc_items][1][subform][field_utexas_pl_link][0][uri]' => '/node/' . $basic_page_id,
      // Second Promo List Container.
      'field_flex_page_pl[1][subform][field_utexas_plc_headline][0][value]' => "Test Title of Second Promo List Container",
      // Second Container - first Promo List item instance with no link.
      'files[field_flex_page_pl_1_subform_field_utexas_plc_items_0_subform_field_utexas_pl_image_0]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_pl[1][subform][field_utexas_plc_items][0][subform][field_utexas_pl_headline][0][value]' => 'Second Container First Item Promo List Headline',
      'field_flex_page_pl[1][subform][field_utexas_plc_items][0][subform][field_utexas_pl_copy][0][value]' => 'Second Container First Item Promo List Copy',
      // Second Container - second Promo List item instance with no link.
      'files[field_flex_page_pl_1_subform_field_utexas_plc_items_1_subform_field_utexas_pl_image_0]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_pl[1][subform][field_utexas_plc_items][1][subform][field_utexas_pl_headline][0][value]' => 'Second Container Second Item Promo List Headline',
      'field_flex_page_pl[1][subform][field_utexas_plc_items][1][subform][field_utexas_pl_copy][0][value]' => 'Second Container Second Item Promo List Copy',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // 2. Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_pl[0][subform][field_utexas_plc_items][0][subform][field_utexas_pl_image][0][alt]' => 'Alt A Container 1 Item 1',
      'field_flex_page_pl[0][subform][field_utexas_plc_items][1][subform][field_utexas_pl_image][0][alt]' => 'Alt B Container 1 Item 2',
      'field_flex_page_pl[1][subform][field_utexas_plc_items][0][subform][field_utexas_pl_image][0][alt]' => 'Alt A Container 2 Item 1',
      'field_flex_page_pl[1][subform][field_utexas_plc_items][1][subform][field_utexas_pl_image][0][alt]' => 'Alt B Container 2 Item 2',
    ],
    'edit-submit'
    );
    $node = $this->drupalGetNodeByTitle('Promo List Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    // Verify first Promo List container headline.
    $this->assertRaw('Test Title of First Promo List Container');
    // Verify first container first instance PL item copy, image and headline.
    // Link is external, and should be wrapped around image and headline.
    $this->assertRaw('First Container First Item Promo List Copy');
    $this->assertRaw('utexas_image_style_85w_85h/public/promo_list/image-test.png');
    // Return anchor tag selected by href.
    $external_anchor_tags = $this->getSession()->getPage()->findAll('css', 'a[href="https://www.tylerfahey.com"]');
    $this->assertTrue(count($external_anchor_tags) == 2);
    $this->assertTrue(strpos($external_anchor_tags[0]->getHtml(), '<picture>'));
    $this->assertTrue(strpos($external_anchor_tags[1]->getHtml(), 'First Container First Item Promo List Headline'));
    $this->assertRaw('alt="Alt A Container 1 Item 1"');

    // Verify first container second instance PL item copy, image and headline.
    // Link is internal, and should be wrapped around image and headline.
    // Return anchor tags selected by href.
    $internal_anchor_tags = $this->getSession()->getPage()->findAll('css', 'a[href="/test-basic-page"]');
    $this->assertTrue(count($internal_anchor_tags) == 2);
    $this->assertTrue(strpos($internal_anchor_tags[0]->getHtml(), '<picture>'));
    $this->assertTrue(strpos($internal_anchor_tags[1]->getHtml(), 'First Container Second Item Promo List Headline'));
    $this->assertRaw('First Container Second Item Promo List Copy');
    $this->assertRaw('alt="Alt B Container 1 Item 2"');

    // Verify second Promo List container headline.
    $this->assertRaw('Test Title of Second Promo List Container');

    // Verify second container first instance PL item copy, image and headline.
    // Link is not present.
    $this->assertRaw('Second Container First Item Promo List Copy');
    $this->assertRaw('utexas_image_style_85w_85h/public/promo_list/image-test.png');
    $this->assertRaw('alt="Alt A Container 2 Item 1"');
    $paragraph_containers = $this->getSession()->getPage()->findAll('css', 'div.paragraph--type--utexas-promo-list-container');
    $this->assertFalse(strpos($paragraph_containers[1]->getHtml(), 'href'));

    // Verify second container second instance PL item copy, image and headline.
    // Link is not present.
    $this->assertRaw('Second Container Second Item Promo List Copy');
    $this->assertTrue(strpos($paragraph_containers[1]->getHtml(), '<picture>'));
    $this->assertTrue(strpos($paragraph_containers[1]->getHtml(), 'Second Container Second Item Promo List Headline'));
    $this->assertRaw('alt="Alt B Container 2 Item 2"');
    // Sign out!
    $this->drupalLogout();
  }

}
