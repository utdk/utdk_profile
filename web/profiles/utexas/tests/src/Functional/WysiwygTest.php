<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies WYSIWYG field schema, validation, & output.
 *
 * @group utexas
 */
class WysiwygTest extends BrowserTestBase {
  use InstallTestTrait;
  use EntityTestTrait;
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
    // 2. Add the WYSIWYG paragraph type.
    // 3. Verify the correct field schema exist.
    $fields = [
      'edit-field-flex-page-wysiwyg-a-0-value',
      'edit-field-flex-page-wysiwyg-b-0-value',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
  }

  /**
   * Test output.
   */
  public function testOutput() {
    $this->assertAllowed("/node/add/utexas_flex_page");

    $edit = [
      'title[0][value]' => 'WYSIWYG Test',
      'field_flex_page_wysiwyg_a[0][value]' => 'WYSIWYG A Body',
      'field_flex_page_wysiwyg_b[0][value]' => 'WYSIWYG B Body',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $node = $this->drupalGetNodeByTitle('WYSIWYG Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertRaw('WYSIWYG A Body');
    $this->assertRaw('WYSIWYG B Body');

    // Sign out!
    $this->drupalLogout();
  }

}
