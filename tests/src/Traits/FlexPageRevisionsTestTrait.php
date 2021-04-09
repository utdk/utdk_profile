<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies that Flex Pages can be revised in a Drupal way.
 */
trait FlexPageRevisionsTestTrait {

  /**
   * Test that revisioning works per Drupal convention.
   */
  public function verifyRevisioning() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $session = $this->getSession();

    // Generate a test node for testing that revisions can be accessed.
    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // Go to layout tab for node.
    $this->drupalGet('node/' . $flex_page . '/layout');
    // Add a new resources block.
    $this->clickLink('Add block');
    $assert->waitForText('Create custom block');
    $this->clickLink('Create custom block');
    $assert->waitForText('Add a new Inline Block');
    $this->clickLink('Basic block');
    // Verify that the add block has been opened in the modal.
    $assert->waitForText('Block description');
    // Fill block title.
    $page->fillField('settings[label]', 'Revision 1');
    $page->pressButton('Add block');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('The layout override has been saved.');

    // Make a revision to the inline block.
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-inline-blockbasic', 'Configure');
    $assert->waitForText('Block description');
    $page->fillField('settings[label]', 'Revision 2');
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('The layout override has been saved.');

    // Revert to first revision.
    $this->drupalGet('node/' . $flex_page . '/revisions');
    $revisions = $page->findAll('css', 'li.revert a');
    $revisions[0]->click();
    $page->pressButton('Revert');

    // Observe that the page has the "Revision 1" again
    // (this is correct behavior, it means the revert worked)
    $this->drupalGet('node/' . $flex_page);
    $assert->pageTextContains('Revision 1');

    // Make a revision to the inline block.
    // Edit block, and change title to anything
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-inline-blockbasic', 'Configure');
    $assert->waitForText('Block description');
    $page->fillField('settings[label]', 'Revision 3');
    $page->pressButton('Update');
    // Without patch from #1122, saving the block will yield a validation error.
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('Revision 3');

    // Clean configuration introduced by test.
    $this->drupalGet('node/' . $flex_page . '/delete');
    $this->submitForm([], 'Delete');

  }

}
