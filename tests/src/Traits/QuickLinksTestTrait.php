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
    $basic_page_id = $this->createBasicPage();
    $this->drupalGet('block/add/utexas_quick_links');
    // Add the Quick Links block type.
    $this->getSession()->getPage()->find('css', '#edit-field-block-ql-0-links-actions-add-link')->click();
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.js-form-item-field-block-ql-0-links-1-url');
    $this->submitForm([
      'info[0][value]' => 'Quick Links Test',
      'field_block_ql[0][headline]' => 'Quick Links Headline',
      'field_block_ql[0][links][0][title]' => 'Quick Links Link!',
      'field_block_ql[0][links][0][url]' => 'https://tylerfahey.com',
      'field_block_ql[0][links][1][title]' => 'Quick Links Link Number 2!',
      'field_block_ql[0][links][1][url]' => '/node/' . $basic_page_id,
    ], 'Save');
    $assert->pageTextContains('Quick Links Quick Links Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');
    $this->drupalGet('<front>');
    // Verify Quick Links headline, is present.
    $this->assertRaw('Quick Links Headline');
    // Verify Quick Links link, delta 0, is present, and is an external link.
    $this->assertRaw('<a href="https://tylerfahey.com" class="ut-link">Quick Links Link!</a>');
    // Verify Quick Links link, delta 1, is present, and is an internal link.
    $this->assertRaw('<a href="/test-basic-page" class="ut-link">Quick Links Link Number 2!</a>');
    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/quicklinkstest/delete');
    $this->submitForm([], 'Remove');
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple([$basic_page_id]);
    $storage_handler->delete($entities);
  }

}
