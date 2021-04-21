<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Defines testing for Image Link widget.
 */
trait ImageLinkTestTrait {

  /**
   * Test schema.
   */
  public function verifyImageLink() {

    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $session = $this->getSession();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Image Link';
    $block_name = 'Image Link Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Open the media library.
    $session->wait(3000);
    $page->pressButton('Add media');
    $session->wait(3000);
    $this->assertNotEmpty($assert->waitForText('Add or select media'));
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));

    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_il[0][link][uri]' => 'https://imagelink.test',
      'field_block_il[0][link][title]' => 'Alt value',
      'field_block_il[0][link][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_il[0][link][options][attributes][class]' => 'ut-cta-link--external',
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

    // Verify page output.
    $assert->linkByHrefExists('https://imagelink.test');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_500w/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="https://imagelink.test"] picture img', 'src', $expected_path);
    // Verify responsive image alt attribute is pulled from link title.
    $assert->elementAttributeContains('css', 'a[href^="https://imagelink.test"] picture img', 'alt', 'Alt value');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');
    $assert->elementExists('css', '.ut-cta-link--external');

    // CRUD: UPDATE.
    // Test internal links.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();

    $this->submitForm([
      'field_block_il[0][link][uri]' => '/node/' . $flex_page,
    ], 'Save');

    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been updated.');
    $this->drupalGet('node/' . $flex_page);

    // Verify page output.
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_500w/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="/test-flex-page"] picture img', 'src', $expected_path);

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
