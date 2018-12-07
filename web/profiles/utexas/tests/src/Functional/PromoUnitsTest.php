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

    // 2. Verify the correct field schema exist.
    $fields = [
      'edit-field-flex-page-pu-0-headline',
      'edit-field-flex-page-pu-0-link-fieldset-promo-unit-items-0-item-headline',
      'edit-field-flex-page-pu-0-link-fieldset-promo-unit-items-0-item-image-upload',
      'edit-field-flex-page-pu-0-link-fieldset-promo-unit-items-0-item-copy-value',
      'edit-field-flex-page-pu-0-link-fieldset-promo-unit-items-0-item-link-url',
      'edit-field-flex-page-pu-0-link-fieldset-promo-unit-items-0-item-link-title',
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
    // 1. Add the Promo Unit.
    $edit = [
      'title[0][value]' => 'Promo Unit Test',
      'field_flex_page_pu[0][headline]' => "Test Title of Promo Unit Container",
      'files[field_flex_page_pu_0_link-fieldset_promo_unit_items_0_item_image]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_pu[0][link-fieldset][promo_unit_items][0][item][headline]' => 'Promo Unit Headline',
      'field_flex_page_pu[0][link-fieldset][promo_unit_items][0][item][copy][value]' => 'Promo Unit Copy',
      'field_flex_page_pu[0][link-fieldset][promo_unit_items][0][item][link][url]' => 'https://www.tylerfahey.com',
      'field_flex_page_pu[0][link-fieldset][promo_unit_items][0][item][link][title]' => 'Test Promo Unit Link',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // TODO: test for alt text once it is implemented.
    $node = $this->drupalGetNodeByTitle('Promo Unit Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);

    // 3. Verify Promo Unit data is present, that the link is external and that image has landscape orientation.
    $this->assertRaw('Test Title of Promo Unit Container');
    $this->assertRaw('utexas_image_style_176w_112h/public/promo_unit_items/image-test.png');
    $this->assertRaw('Promo Unit Headline');
    $this->assertRaw('Promo Unit Copy');
    $this->assertRaw('Test Promo Unit Link');
    $this->assertRaw('<a href="https://www.tylerfahey.com"');

    // Test for portrait image based on user selection.
    $this->drupalGet('node/' . $node->id() . '/layout');
    // Add layout section.
    $this->getSession()->getPage()->find('css', 'a.use-ajax.new-section__link')->click();
    $this->getSession()->getPage()->find('css', 'ul.layout-selection li:nth-child(2) a')->click();
    $this->getSession()->getPage()->find('css', '#edit-actions-submit')->click();
    // Add promo unit to left section region.
    $this->getSession()->getPage()->find('css', 'div.layout__region--left a.use-ajax.new-block__link')->click();
    $this->getSession()->getPage()->find('css', 'summary:contains("Content") + div ul.links a:contains("Promo Unit")')->click();
    $edit = [
      'settings[formatter][type]' => 'utexas_promo_unit_2',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-actions-submit');
    // Add promo unit to right section region.
    $this->getSession()->getPage()->find('css', 'div.layout__region--right a.use-ajax.new-block__link')->click();
    $this->getSession()->getPage()->find('css', 'summary:contains("Content") + div ul.links a:contains("Promo Unit")')->click();
    $edit = [
      'settings[formatter][type]' => 'utexas_promo_unit_3',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-actions-submit');
    // Save layout.
    $this->getSession()->getPage()->find('css', 'a:contains("Save Layout")')->click();

    // Test for presence of portrait and square images.
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('utexas_image_style_120w_150h/public/promo_unit_items/image-test.png');
    $this->assertRaw('utexas_image_style_112w_112h/public/promo_unit_items/image-test.png');

    // Sign out!
    $this->drupalLogout();
  }

}
