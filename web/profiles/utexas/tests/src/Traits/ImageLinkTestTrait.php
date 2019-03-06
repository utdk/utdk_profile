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
    $this->clickLink('Add media');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Media library');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonpane')->pressButton('Select media');
    $assert->assertWaitOnAjaxRequest();

    $this->submitForm([
      'info[0][value]' => 'Image Link Test',
      'field_block_il[0][link][url]' => 'https://imagelink.test',
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

    $this->drupalGet('admin/structure/block/manage/imagelinktest/delete');
    $this->submitForm([], 'Remove');

    // Test internal links.
    $basic_page_id = $this->createBasicPage();
    $this->drupalGet('block/add/utexas_image_link');
    $this->clickLink('Add media');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Media library');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonpane')->pressButton('Select media');
    $assert->assertWaitOnAjaxRequest();

    $this->submitForm([
      'info[0][value]' => 'Image Link Test 2',
      'field_block_il[0][link][url]' => '/node/' . $basic_page_id,
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
    $assert->elementAttributeContains('css', 'a[href^="/node/1"] picture img', 'src', $expected_path);

    $this->drupalGet('admin/structure/block/manage/imagelinktest2/delete');
    $this->submitForm([], 'Remove');
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple([$basic_page_id]);
    $storage_handler->delete($entities);
  }

}
