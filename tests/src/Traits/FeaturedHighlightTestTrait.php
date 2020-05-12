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
    $this->drupalGet('block/add/utexas_featured_highlight');

    // Verify widget field schema.
    $page->pressButton('Add media');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Add or select media');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $assert->assertWaitOnAjaxRequest();

    $this->submitForm([
      'info[0][value]' => 'Featured Highlight Test',
      'field_block_featured_highlight[0][headline]' => 'Featured Highlight Headline',
      'field_block_featured_highlight[0][copy][value]' => 'Featured Highlight Copy',
      'field_block_featured_highlight[0][cta_wrapper][link][uri]' => 'https://featuredhighlight.test',
      'field_block_featured_highlight[0][cta_wrapper][link][title]' => 'Featured Highlight Link',
      'field_block_featured_highlight[0][cta_wrapper][link][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_featured_highlight[0][cta_wrapper][link][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_featured_highlight[0][date]' => '01-17-2019',
    ], 'Save');
    $assert->pageTextContains('Featured Highlight Featured Highlight Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementTextContains('css', 'h2.ut-headline', 'Featured Highlight Headline');
    $assert->pageTextContains('Featured Highlight Copy');
    $assert->linkByHrefExists('https://featuredhighlight.test');
    $assert->pageTextContains('Jan. 17, 2019');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_500w_300h/public/image-test';
    $assert->elementAttributeContains('css', 'a[href^="https://featuredhighlight.test"] picture img', 'src', $expected_path);
    // Verify link exists with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');

    // Set display to "Bluebonnet (Medium)".
    $this->drupalGet('admin/structure/block/manage/featuredhighlighttest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_featured_highlight_2',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', 'div.utexas-featured-highlight.medium');

    // Set display to "Charcoal (Dark)".
    $this->drupalGet('admin/structure/block/manage/featuredhighlighttest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_featured_highlight_3',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', 'div.utexas-featured-highlight.dark');

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/featuredhighlighttest/delete');
    $this->submitForm([], 'Remove');

    // Test rendering of YouTube video.
    $this->drupalGet('block/add/utexas_featured_highlight');

    // Verify widget field schema.
    $page->pressButton('Add media');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Add or select media');
    $this->clickLink("Video (External)");
    $assert->assertWaitOnAjaxRequest();

    $assert->pageTextContains('Video 1');
    // Select the 1st video media item (should be "Video 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $assert->assertWaitOnAjaxRequest();

    $this->submitForm([
      'info[0][value]' => 'Featured Highlight Video Test',
      'field_block_featured_highlight[0][headline]' => 'Featured Highlight Headline',
      'field_block_featured_highlight[0][copy][value]' => 'Featured Highlight Copy',
      'field_block_featured_highlight[0][cta_wrapper][link][uri]' => 'https://featuredhighlight.test',
      'field_block_featured_highlight[0][cta_wrapper][link][title]' => 'Featured Highlight Link',
      'field_block_featured_highlight[0][date]' => '01-17-2019',
    ], 'Save');
    $assert->pageTextContains('Featured Highlight Featured Highlight Video Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'src', "/media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3DdQw4w9WgXcQ");
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'width', "100%");
    $assert->elementAttributeContains('css', '.utexas-featured-highlight iframe', 'height', "100%");

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/featuredhighlightvideotest/delete');
    $this->submitForm([], 'Remove');
  }

}
