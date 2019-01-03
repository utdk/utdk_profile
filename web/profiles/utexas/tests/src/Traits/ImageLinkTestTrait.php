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
    $this->drupalGet('node/add/utexas_flex_page');

    // Verify that both media widget instances are present.
    $assert->pageTextContains('Image Link A');
    $image_link_a_wrapper = $assert->elementExists('css', '#edit-field-flex-page-il-a-base');
    $image_link_a_wrapper->click();
    $image_link_a_button = $assert->elementExists('css', '#edit-field-flex-page-il-a-0-image-media-library-open-button');
    $image_link_a_button->click();
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Media library');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonpane')->pressButton('Select media');
    $assert->assertWaitOnAjaxRequest();

    $assert->pageTextContains('Image Link B');
    $image_link_a_wrapper = $assert->elementExists('css', '#edit-field-flex-page-il-b-base');
    $image_link_a_wrapper->click();
    $image_link_a_button = $assert->elementExists('css', '#edit-field-flex-page-il-b-0-image-media-library-open-button');
    $image_link_a_button->click();
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
      'title[0][value]' => 'Image Link Test',
      'field_flex_page_il_a[0][link][url]' => 'https://imagelink.test',
      'field_flex_page_il_b[0][link][url]' => '/node/1',
    ], 'Save');

    // Verify page output.
    $assert->linkByHrefExists('https://imagelink.test');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_500w/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="https://imagelink.test"] picture img', 'src', $expected_path);
    // Verify Image Link B link is internal.
    $assert->linkByHrefExists('/image-link-test');

    // Delete the node from the system.
    $node = $this->drupalGetNodeByTitle('Image Link Test');
    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->submitForm([], 'Delete');
  }

}
