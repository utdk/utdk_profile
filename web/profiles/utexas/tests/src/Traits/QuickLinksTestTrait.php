<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies QuickLinks field schema & validation.
 */
trait QuickLinksTestTrait {

  /**
   * Test Quick Links.
   */
  public function verifyQuickLinks() {
    $assert = $this->assertSession();
    // 1. Verify a user has access to the content type.
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 2. Verify the correct field schemae exist.
    $fields = [
      'edit-field-flex-page-ql-0-headline',
      'edit-field-flex-page-ql-0-copy-value',
      'edit-field-flex-page-ql-0-links-0-url',
      'edit-field-flex-page-ql-0-links-0-title',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }

    $this->assertAllowed("/node/add/utexas_flex_page");
    // 1. Add the Quick Links paragraph type.
    $edit = [
      'title[0][value]' => 'Quick Links Test',
      'field_flex_page_ql[0][headline]' => 'Quick Links Headline',
      'field_flex_page_ql[0][copy][value]' => 'Quick Links Copy Value',
      'field_flex_page_ql[0][links][0][title]' => 'Quick Links Link!',
      'field_flex_page_ql[0][links][0][url]' => 'https://tylerfahey.com',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $node = $this->drupalGetNodeByTitle('Quick Links Test');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // 2. Edit the node and add a second link.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-ql-0-links-actions-add-link')->click();
    $this->drupalPostForm(NULL, [
      'field_flex_page_ql[0][links][1][title]' => 'Quick Links Link Number 2!',
      'field_flex_page_ql[0][links][1][url]' => '/node/' . $basic_page_id,
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

    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->submitForm([], 'Delete');
  }

}
