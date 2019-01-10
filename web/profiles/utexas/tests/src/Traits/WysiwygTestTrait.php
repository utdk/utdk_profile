<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies that Wysiwyg A & B exist on the Flex Page content type.
 */
trait WysiwygTestTrait {

  /**
   * Test that revisioning works per Drupal convention.
   */
  public function verifyWysiwyg() {
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

    // Clean configuration introduced by test.
    $node = $this->drupalGetNodeByTitle('WYSIWYG Test');
    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->submitForm([], 'Delete');
  }

}
