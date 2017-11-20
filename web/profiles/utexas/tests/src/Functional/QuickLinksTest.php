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
    // 2. Verify the correct field schemae exist.
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
    $edit = [
      'title[0][value]' => 'Quick Links Test',
      'field_flex_page_ql[0][subform][field_utexas_ql_headline][0][value]' => 'Quick Links Headline',
      'field_flex_page_ql[0][subform][field_utexas_ql_copy][0][value]' => 'Quick Links Copy Value',
      'field_flex_page_ql[0][subform][field_utexas_ql_links][0][title]' => 'Quick Links Link!',
      'field_flex_page_ql[0][subform][field_utexas_ql_links][0][uri]' => 'https://tylerfahey.com',
    ];
    $this->drupalPostForm("/node/add/utexas_flex_page", $edit, 'edit-submit');
    $node = $this->drupalGetNodeByTitle('Quick Links Test');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // Verify we can add a second link item to Quick Links instance.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-ql-0-subform-field-utexas-ql-links-add-more')->click();

    $this->drupalPostForm(NULL, [
      'field_flex_page_ql[0][subform][field_utexas_ql_links][1][title]' => 'Quick Links Link Number 2!',
      'field_flex_page_ql[0][subform][field_utexas_ql_links][1][uri]' => 'https://quicklinks.com',
    ],
      'edit-submit');
    $node = $this->drupalGetNodeByTitle('Quick Links Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    // Verify Quick Links link, delta 0, is present.
    $this->assertRaw('Quick Links Headline');
    $this->assertRaw('Quick Links Copy Value');
    $this->assertRaw('<a href="https://tylerfahey.com">Quick Links Link!</a>');
    // Verify Quick Links link, delta 1, is present.
    $this->assertRaw('<a href="https://quicklinks.com">Quick Links Link Number 2!</a>');

    // Sign out!
    $this->drupalLogout();
  }

}
