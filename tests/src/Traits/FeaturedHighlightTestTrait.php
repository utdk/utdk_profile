<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Featured Highlight field schema & output.
 */
trait FeaturedHighlightTestTrait {

  /**
   * Test schema.
   */
  public function verifyFeaturedHighlight() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $session = $this->getSession();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Featured Highlight';
    $block_name = $block_type . 'Test';
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
      'field_block_featured_highlight[0][headline]' => 'Featured Highlight Headline',
      'field_block_featured_highlight[0][copy][value]' => '<h3>Heading text</h3><p>Featured Highlight copy</p>',
      'field_block_featured_highlight[0][cta_wrapper][link][uri]' => 'https://featuredhighlight.test',
      'field_block_featured_highlight[0][cta_wrapper][link][title]' => 'Featured Highlight Link',
      'field_block_featured_highlight[0][cta_wrapper][link][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_featured_highlight[0][cta_wrapper][link][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_featured_highlight[0][date]' => '01-17-2019',
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
    $assert->elementTextContains('css', 'h2.ut-headline a', 'Featured Highlight Headline');
    $assert->pageTextContains('Featured Highlight Copy');
    $assert->linkByHrefExists('https://featuredhighlight.test');
    $assert->pageTextContains('Jan. 17, 2019');
    // Verify responsive image is present.
    $assert->elementExists('css', '.utexas-featured-highlight .image-wrapper picture source');
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.utexas-featured-highlight .image-wrapper a picture source');
    // Verify expected image.
    $expected_path = 'utexas_image_style_500w_300h/public/image-test';
    $assert->elementAttributeContains('css', '.utexas-featured-highlight .image-wrapper picture img', 'src', $expected_path);
    // // Verify link exists with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');
    // Verify CTA not tabbable when headline and link present.
    $assert->elementAttributeContains('css', '.ut-btn.ut-cta-link--external', 'tabindex', '-1');

    // Set display to "Bluebonnet (Medium)".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_featured_highlight_2',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('The layout override has been saved.');

    // Verify page output.
    $assert->elementExists('css', 'div.utexas-featured-highlight.medium');
    // Verify headings in copy field are white.
    $medium_copy_color = $this->getSession()->evaluateScript('jQuery(".utexas-featured-highlight.medium .ut-copy h3").css("color")');
    $this->assertSame("rgb(255, 255, 255)", $medium_copy_color);

    // Set display to "Charcoal (Dark)".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_featured_highlight_3',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('The layout override has been saved.');
    // Verify page output.
    $assert->elementExists('css', 'div.utexas-featured-highlight.dark');
    // Verify headings in copy field are white.
    $dark_copy_color = $this->getSession()->evaluateScript('jQuery(".utexas-featured-highlight.dark .ut-copy h3").css("color")');
    $this->assertSame("rgb(255, 255, 255)", $dark_copy_color);

    // Test rendering of YouTube video.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $page->pressButton('media-0-media-library-remove-button-field_block_featured_highlight-0');
    $this->assertNotEmpty($assert->waitForText('One media item remaining.'));
    $page->pressButton('Add media');
    $this->assertNotEmpty($assert->waitForText('Add or select media'));
    $this->clickLink("Video (External)");
    $this->assertNotEmpty($assert->waitForText('Add Video (External) via URL'));

    $assert->pageTextContains('Video 1');
    // Select the 1st video media item (should be "Video 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));
    $this->submitForm([], 'Save');

    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been updated.');

    $this->drupalGet('node/' . $flex_page);
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'src', "/media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3DdQw4w9WgXcQ");
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'width', "100%");
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'height', "100%");

    // Confirm that the inner iframe has a title attribute.
    // See https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/1201.
    $inner_frame = 'frames[0].document.querySelector("iframe")';
    $this->assertSame('YouTube content: Rick Astley - Never Gonna Give You Up (Official Music Video)', $session->evaluateScript("$inner_frame.getAttribute('title')"));

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
