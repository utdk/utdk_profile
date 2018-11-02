<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Flex Content Area A & B field schema & validation.
 *
 * @group utexas
 */
class FlexContentAreaTest extends BrowserTestBase {

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
    // 2. Add the Flex Content Area A & B paragraph types.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-fca-a-add-more-add-more-button-utexas-fca-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-fca-b-add-more-add-more-button-utexas-fca-container')->click();
    // 3. Verify the correct field schemae exist.
    $fields = [
      'edit-field-flex-page-fca-a-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-image-0-upload',
      'edit-field-flex-page-fca-a-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-headline-0-value',
      'edit-field-flex-page-fca-a-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-copy-0-value',
      'edit-field-flex-page-fca-a-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-links-0-uri',
      'edit-field-flex-page-fca-a-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-links-0-title',
      'edit-field-flex-page-fca-a-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-cta-0-uri',
      'edit-field-flex-page-fca-a-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-cta-0-title',
      'edit-field-flex-page-fca-b-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-image-0-upload',
      'edit-field-flex-page-fca-b-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-headline-0-value',
      'edit-field-flex-page-fca-b-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-copy-0-value',
      'edit-field-flex-page-fca-b-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-links-0-uri',
      'edit-field-flex-page-fca-b-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-links-0-title',
      'edit-field-flex-page-fca-b-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-cta-0-uri',
      'edit-field-flex-page-fca-b-0-subform-field-utexas-fca-items-0-subform-field-utexas-fca-cta-0-title',
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
    // 1. Add the Flex Content Area A & B paragraph types.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-fca-a-add-more-add-more-button-utexas-fca-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-fca-b-add-more-add-more-button-utexas-fca-container')->click();
    // 2. Submit an image with no alt text & CTA with no title.
    $edit = [
      'title[0][value]' => 'Flex Page Test',
      'files[field_flex_page_fca_a_0_subform_field_utexas_fca_items_0_subform_field_utexas_fca_image_0]' => drupal_realpath($this->testImage),
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_cta][0][uri]' => 'https://markfullmer.com',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    // 3. Images must have alt text!
    $this->assertRaw('Alternative text field is required.');
    $this->assertRaw('Link text field is required if there is URL input.');
  }

  /**
   * Test output.
   */
  public function testOutput() {
    // Generate a test node for referencing an internal link.
    $basic_page_id = $this->createBasicPage();
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 1. Add the Flex Content Area A & B paragraph types.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-fca-a-add-more-add-more-button-utexas-fca-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-fca-b-add-more-add-more-button-utexas-fca-container')->click();

    $edit = [
      'title[0][value]' => 'Flex Content Area Test',
      'files[field_flex_page_fca_a_0_subform_field_utexas_fca_items_0_subform_field_utexas_fca_image_0]' => drupal_realpath($this->testImage),
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_headline][0][value]' => 'FCA A Headline!',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_copy][0][value]' => 'FCA A Copy',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_links][0][uri]' => 'https://markfullmer.com',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_links][0][title]' => 'FCA A Link 1',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_cta][0][uri]' => 'https://pantheon.io',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_cta][0][title]' => 'FCA A CTA',
      'files[field_flex_page_fca_b_0_subform_field_utexas_fca_items_0_subform_field_utexas_fca_image_0]' => drupal_realpath($this->testImage),
      'field_flex_page_fca_b[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_headline][0][value]' => 'FCA B Headline!',
      'field_flex_page_fca_b[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_copy][0][value]' => 'FCA B Copy',
      'field_flex_page_fca_b[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_links][0][uri]' => 'https://markfullmer.com',
      'field_flex_page_fca_b[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_links][0][title]' => 'FCA B Link 1',
      'field_flex_page_fca_b[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_cta][0][uri]' => 'https://pantheon.io',
      'field_flex_page_fca_b[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_cta][0][title]' => 'FCA B CTA',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // 2. Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_image][0][alt]' => 'Alt text',
      'field_flex_page_fca_b[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_image][0][alt]' => 'Alt text',
    ],
    'edit-submit');

    $node = $this->drupalGetNodeByTitle('Flex Content Area Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);

    // 3. Verify Flex Content A, delta 0, is present.
    $this->assertRaw('FCA A Headline');
    $this->assertRaw('FCA A Copy');
    // External links must be allowed in the Links field.
    $this->assertRaw('<a href="https://markfullmer.com" class="ut-link">FCA A Link 1</a>');
    // External links must be allowed in the CTA field.
    $this->assertRaw('<a href="https://pantheon.io" class="ut-btn--small">FCA A CTA</a>');
    // Return all picture tags.
    $picture_tags = $this->getSession()->getPage()->findAll('css', 'picture');
    // Verify there are two picture tags created by the test.
    $this->assertTrue(count($picture_tags) == 2);
    // Verify first picture tag contains correct filename.
    $image1 = $picture_tags[0]->getHtml();
    $this->assertTrue(strpos($image1, 'flex_content_area/image-test'));

    // 4. Verify Flex Content B is present.
    $this->assertRaw('FCA B Headline');
    $this->assertRaw('FCA B Copy');
    $this->assertRaw('<a href="https://markfullmer.com" class="ut-link">FCA B Link 1</a>');
    $this->assertRaw('<a href="https://pantheon.io" class="ut-btn--small">FCA B CTA</a>');
    // Verify second picture tag contains correct filename.
    $image2 = $picture_tags[1]->getHtml();
    $this->assertTrue(strpos($image2, 'flex_content_area/image-test'));

    // Edit the node to add a second FCA instance and link.
    $this->drupalGet('node/' . $node->id() . '/edit');

    // 5. Verify we can add a second Flex Content Area A instance.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-fca-a-0-subform-field-utexas-fca-items-add-more-add-more-button-utexas-flex-content-area')->click();

    $this->drupalPostForm(NULL, [
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_links][1][uri]' => 'https://genderedtextproject.com',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][0][subform][field_utexas_fca_links][1][title]' => 'FCA A Link 2',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][1][subform][field_utexas_fca_headline][0][value]' => 'FCA A #2 Headline!',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][1][subform][field_utexas_fca_copy][0][value]' => 'FCA A #2 Copy',
      // Test that both the path alias and the internal node id can be used.
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][1][subform][field_utexas_fca_links][0][uri]' => '/test-basic-page',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][1][subform][field_utexas_fca_links][0][title]' => 'Link to Basic Page',
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][1][subform][field_utexas_fca_cta][0][uri]' => '/node/' . $basic_page_id,
      'field_flex_page_fca_a[0][subform][field_utexas_fca_items][1][subform][field_utexas_fca_cta][0][title]' => 'CTA to Basic Page',
    ],
    'edit-submit');

    $this->drupalGet('node/' . $node->id());

    // A second link must be possible.
    $this->assertRaw('<a href="https://genderedtextproject.com" class="ut-link">FCA A Link 2</a>');
    // 6. Verify Flex Content A, delta 1, is present.
    $this->assertRaw('FCA A #2 Headline');
    $this->assertRaw('FCA A #2 Copy');
    // Internal links must be allowed in the Links field & display as aliases.
    $this->assertRaw('<a href="/test-basic-page" class="ut-link">Link to Basic Page</a>');
    // Internal links must be allowed in the CTA field & display as aliases.
    $this->assertRaw('<a href="/test-basic-page" class="ut-btn--small">CTA to Basic Page</a>');

    // Sign out!
    $this->drupalLogout();
  }

}
