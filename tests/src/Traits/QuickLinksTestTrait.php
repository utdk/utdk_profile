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

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Quick Links';
    $block_name = 'Quick Links Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Add the Quick Links block type.
    $this->getSession()->getPage()->find('css', '#edit-field-block-ql-0-links-actions-add-link')->click();
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_ql[0][links][1][uri]',
    ]));

    $assert->elementExists('css', '.js-form-item-field-block-ql-0-links-1-uri');
    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_ql[0][headline]' => 'Quick Links Headline',
      'field_block_ql[0][links][0][title]' => 'Quick Links Link!',
      'field_block_ql[0][links][0][uri]' => 'https://tylerfahey.com',
      'field_block_ql[0][links][0][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_ql[0][links][0][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_ql[0][links][1][title]' => 'Quick Links Link Number 2!',
      'field_block_ql[0][links][1][uri]' => '/node/' . $flex_page,
      'field_block_ql[0][links][1][options][attributes][class]' => 'ut-cta-link--lock',
    ], 'Save');
    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been created.');

    // Place the block on the Flex page.
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickLink('Add block');
    $this->assertNotEmpty($assert->waitForText('Create custom block'));
    $this->clickLink($block_name);
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $page->pressButton('Add block');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('The layout override has been saved.');

    // Verify Quick Links headline, is present.
    $this->assertRaw('Quick Links Headline');
    // Verify Quick Links link, delta 0, is present, is an external link, and
    // has appropriate options.
    $this->assertRaw('<a href="https://tylerfahey.com" rel="noopener noreferrer" class="ut-cta-link--external ut-link" target="_blank">Quick Links Link!</a>');
    // Verify Quick Links link, delta 1, is present, is an internal link, and
    // has appropriate options.
    $this->assertRaw('<a href="/test-flex-page" class="ut-cta-link--lock ut-link">Quick Links Link Number 2!</a>');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    // Click button to add new link.
    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_ql[0][links][2][uri]',
    ]));
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

    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    // Confirm second link has data from third link previously created.
    $assert->fieldValueEquals('field_block_ql[0][links][1][title]', 'Third link');
    $assert->fieldValueEquals('field_block_ql[0][links][1][uri]', 'https://quicklinks.test');
    // Assert former second link is now gone.
    $assert->pageTextNotContains('Quick Links Link Number 2!');

    // CRUD: DELETE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $page->clickLink('Delete');
    $page->pressButton('Delete');
    $this->drupalGet('admin/structure/block/block-content');
    $assert->pageTextNotContains($block_name);

    // TEST CLEANUP //
    // Remove test page.
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple([$flex_page]);
    $storage_handler->delete($entities);
  }

}
