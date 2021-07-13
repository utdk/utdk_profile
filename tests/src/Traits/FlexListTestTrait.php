<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Test input/output of Flex List field type via the Flex List block type.
 */
trait FlexListTestTrait {

  /**
   * Test schema.
   */
  public function verifyFlexList() {

    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $session = $this->getSession();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Flex List';
    $block_name = $block_type . ' test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);
    $this->assertNotEmpty($assert->waitForText('Add Flex List custom block'));
    $page->pressButton('Add another item');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '[name="field_utexas_flex_list_items[1][header]"]'));
    $this->getSession()->getPage()->fillField('info[0][value]', $block_name);
    $this->getSession()->getPage()->fillField('field_utexas_flex_list_items[0][header]', 'Location');
    $this->getSession()->getPage()->fillField('field_utexas_flex_list_items[0][content][format]', 'restricted_html');
    $this->getSession()->getPage()->fillField('field_utexas_flex_list_items[1][content][format]', 'restricted_html');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '[name="field_utexas_flex_list_items[0][content][value]"]'));
    $this->getSession()->getPage()->fillField('field_utexas_flex_list_items[0][content][value]', 'FAC 326');
    $this->getSession()->getPage()->fillField('field_utexas_flex_list_items[1][header]', 'Website');
    $this->getSession()->getPage()->fillField('field_utexas_flex_list_items[1][content][value]', 'https://drupalkit.its.utexas.edu');
    $page->pressButton('Save');
    $this->assertNotEmpty($assert->waitForText($block_type . ' ' . $block_name . ' has been created.'));

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

    // Verify page output.
    $assert->linkByHrefExists('https://drupalkit.its.utexas.edu');
    $assert->elementExists('css', '.utexas-flex-list--item h5#location');
    $assert->elementExists('css', '.utexas-flex-list--item h5#website');

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
