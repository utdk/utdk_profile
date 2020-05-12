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

    $this->drupalGet('block/add/utexas_image_link');
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
      'info[0][value]' => 'Image Link Test',
      'field_block_il[0][link][uri]' => 'https://imagelink.test',
      'field_block_il[0][link][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_il[0][link][options][attributes][class]' => 'ut-cta-link--external',
    ], 'Save');

    $assert->pageTextContains('Image Link Image Link Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');
    $this->drupalGet('<front>');

    // Verify page output.
    $assert->linkByHrefExists('https://imagelink.test');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_500w/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="https://imagelink.test"] picture img', 'src', $expected_path);
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');
    $assert->elementExists('css', '.ut-cta-link--external');

    $this->drupalGet('admin/structure/block/manage/imagelinktest/delete');
    $this->submitForm([], 'Remove');

    // Test internal links.
    $basic_page_id = $this->createBasicPage();
    $this->drupalGet('block/add/utexas_image_link');
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
      'info[0][value]' => 'Image Link Test 2',
      'field_block_il[0][link][uri]' => '/node/' . $basic_page_id,
    ], 'Save');

    $assert->pageTextContains('Image Link Image Link Test 2 has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');
    $this->drupalGet('<front>');

    // Verify page output.
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_500w/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="/test-basic-page"] picture img', 'src', $expected_path);

    $this->drupalGet('admin/structure/block/manage/imagelinktest2/delete');
    $this->submitForm([], 'Remove');
    // Remove test page.
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple([$basic_page_id]);
    $storage_handler->delete($entities);
  }

}
