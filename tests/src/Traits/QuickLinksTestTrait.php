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
    $page = $this->getSession()->getPage();
    $basic_page_id = $this->createBasicPage();
    $this->drupalGet('block/add/utexas_quick_links');
    // Add the Quick Links block type.
    $this->getSession()->getPage()->find('css', '#edit-field-block-ql-0-links-actions-add-link')->click();
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.js-form-item-field-block-ql-0-links-1-uri');
    $this->submitForm([
      'info[0][value]' => 'Quick Links Test',
      'field_block_ql[0][headline]' => 'Quick Links Headline',
      'field_block_ql[0][links][0][title]' => 'Quick Links Link!',
      'field_block_ql[0][links][0][uri]' => 'https://tylerfahey.com',
      'field_block_ql[0][links][0][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_ql[0][links][0][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_ql[0][links][1][title]' => 'Quick Links Link Number 2!',
      'field_block_ql[0][links][1][uri]' => '/node/' . $basic_page_id,
      'field_block_ql[0][links][1][options][attributes][class]' => 'ut-cta-link--lock',
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
    // Verify Quick Links link, delta 0, is present, is an external link, and
    // has appropriate options.
    $this->assertRaw('<a href="https://tylerfahey.com" rel="noopener noreferrer" class="ut-cta-link--external ut-link" target="_blank">Quick Links Link!</a>');
    // Verify Quick Links link, delta 1, is present, is an internal link, and
    // has appropriate options.
    $this->assertRaw('<a href="/test-basic-page" class="ut-cta-link--lock ut-link">Quick Links Link Number 2!</a>');
    // Edit block to add more links.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Quick Links Test')->click();
    // Click button to add new link twice.
    $page->pressButton('Add link');
    $assert->assertWaitOnAjaxRequest();
    // Fill the third link.
    $page->fillField('field_block_ql[0][links][2][uri]', 'https://quicklinks.test');
    $page->fillField('field_block_ql[0][links][2][title]', 'Third link');
    // Empty second link.
    $page->fillField('field_block_ql[0][links][1][uri]', '');
    $page->fillField('field_block_ql[0][links][1][title]', '');
    $page->fillField('field_block_ql[0][links][1][options][attributes][class]', '0');
    $page->uncheckField('field_block_ql[0][links][1][options][attributes][target][_blank]');
    // Save block data and assert links are reordered.
    $page->pressButton('edit-submit');
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Quick Links Test')->click();
    // Confirm second link has data from third link previously created.
    $this->assertSession()->fieldValueEquals('field_block_ql[0][links][1][title]', 'Third link');
    $this->assertSession()->fieldValueEquals('field_block_ql[0][links][1][uri]', 'https://quicklinks.test');
    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/quicklinkstest/delete');
    $this->submitForm([], 'Remove');
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple([$basic_page_id]);
    $storage_handler->delete($entities);
  }

}
