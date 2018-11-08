<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Flex Content Area A & B field schema & validation.
 *
 * @group utexas
 */
class QuickLinksTest extends BrowserTestBase {
  use EntityTestTrait;
  use UserTestTrait;
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
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeFlexPageEditor();
  }

  /**
   * Test schema.
   */
  public function testSchema() {
    $assert = $this->assertSession();
    // 1. Verify a user has access to the content type.
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 2. Add the Quick Links paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-ql-add-more-add-more-button-utexas-quick-links')->click();
    // 3. Verify the correct field schemae exist.
    $fields = [
      'edit-field-flex-page-ql-0-subform-field-utexas-ql-headline-0-value',
      'edit-field-flex-page-ql-0-subform-field-utexas-ql-copy-0-value',
      'edit-field-flex-page-ql-0-subform-field-utexas-ql-links-0-uri',
      'edit-field-flex-page-ql-0-subform-field-utexas-ql-links-0-title',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Test output.
   */
  public function testOutput() {
    // Generate a test node for referencing an internal link.
    $basic_page_id = $this->createBasicPage();
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 1. Add the Quick Links paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-ql-add-more-add-more-button-utexas-quick-links')->click();
    $edit = [
      'title[0][value]' => 'Quick Links Test',
      'field_flex_page_ql[0][subform][field_utexas_ql_headline][0][value]' => 'Quick Links Headline',
      'field_flex_page_ql[0][subform][field_utexas_ql_copy][0][value]' => 'Quick Links Copy Value',
      'field_flex_page_ql[0][subform][field_utexas_ql_links][0][title]' => 'Quick Links Link!',
      'field_flex_page_ql[0][subform][field_utexas_ql_links][0][uri]' => 'https://tylerfahey.com',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $node = $this->drupalGetNodeByTitle('Quick Links Test');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // 2. Edit the node and add a second link.
    $this->drupalPostForm(NULL, [
      'field_flex_page_ql[0][subform][field_utexas_ql_links][1][title]' => 'Quick Links Link Number 2!',
      'field_flex_page_ql[0][subform][field_utexas_ql_links][1][uri]' => '/node/' . $basic_page_id,
    ],
      'edit-submit');
    $node = $this->drupalGetNodeByTitle('Quick Links Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    // 3. Verify Quick Links headline, is present.
    $this->assertRaw('Quick Links Headline');
    // 4. Verify Quick Links link, delta 0, is present, and is an external link.
    $this->assertRaw('Quick Links Copy Value');
    $this->assertRaw('<a href="https://tylerfahey.com" class="ut-link">Quick Links Link!</a>');
    // 5. Verify Quick Links link, delta 1, is present, and is an internal link.
    $this->assertRaw('<a href="/test-basic-page" class="ut-link">Quick Links Link Number 2!</a>');

    // Sign out!
    $this->drupalLogout();
  }

}
