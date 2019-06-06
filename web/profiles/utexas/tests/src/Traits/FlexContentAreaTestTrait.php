<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Flex Content Area A & B field schema & validation.
 */
trait FlexContentAreaTestTrait {

  /**
   * Test schema.
   */
  public function verifyFlexContentArea() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('block/add/utexas_flex_content_area');

    // Verify widget field schema.
    $page->pressButton('Set media');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Add or select media');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $assert->assertWaitOnAjaxRequest();

    // Add two more instances.
    $page->pressButton('Add another item');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Add another item');
    $assert->assertWaitOnAjaxRequest();

    $this->submitForm([
      'info[0][value]' => 'Flex Content Area Test',
      'field_block_fca[0][headline]' => 'Flex Content Area Headline',
      'field_block_fca[0][copy][value]' => 'Flex Content Area Copy',
      'field_block_fca[0][links][0][url]' => 'https://utexas.edu',
      'field_block_fca[0][links][0][title]' => 'Flex Content Area External Link',
      'field_block_fca[0][cta_wrapper][link][url]' => 'https://utexas.edu',
      'field_block_fca[0][cta_wrapper][link][title]' => 'Flex Content Area Call to Action',
      'field_block_fca[1][headline]' => 'Flex Content Area Headline 2',
      'field_block_fca[1][copy][value]' => 'Flex Content Area Copy 2',
      'field_block_fca[1][links][0][url]' => 'https://utexas.edu',
      'field_block_fca[1][links][0][title]' => 'Flex Content Area External Link 2',
      'field_block_fca[1][cta_wrapper][link][url]' => 'https://utexas.edu',
      'field_block_fca[1][cta_wrapper][link][title]' => 'Flex Content Area Call to Action 2',
      'field_block_fca[2][headline]' => 'Flex Content Area Headline 3',
      'field_block_fca[2][copy][value]' => '',
      'field_block_fca[2][links][0][url]' => '',
      'field_block_fca[2][links][0][title]' => '',
      'field_block_fca[2][cta_wrapper][link][url]' => '',
      'field_block_fca[2][cta_wrapper][link][title]' => '',
    ], 'Save');
    $assert->pageTextContains('Flex Content Area Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');

    // Flex Content Area instance 1 is rendered.
    $assert->elementTextContains('css', 'h3.ut-headline', 'Flex Content Area Headline');
    $assert->pageTextContains('Flex Content Area Copy');
    $assert->linkByHrefExists('https://utexas.edu');
    $assert->elementTextContains('css', 'a.ut-btn--small', 'Flex Content Area Call to Action');
    // Verify responsive image is present within the link.
    $expected_path = 'utexas_image_style_340w_227h/public/image-test.png';
    $assert->elementAttributeContains('css', 'picture img', 'src', $expected_path);

    // Flex Content Area instance 2 is rendered.
    $assert->elementTextContains('css', '.ut-flex-content-area:nth-child(2) h3.ut-headline', 'Flex Content Area Headline 2');
    $assert->pageTextContains('Flex Content Area Copy 2');
    $assert->linkExists('Flex Content Area External Link 2');
    $assert->elementTextContains('css', '.ut-flex-content-area:nth-child(2) a.ut-btn--small', 'Flex Content Area Call to Action 2');

    // Empty Flex Content Area instance 3 elements do not render.
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(3) a.ut-btn--small');
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(3) .ut-copy');
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(3) .link-list');
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(3) .image-wrapper');

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/flexcontentareatest/delete');
    $this->submitForm([], 'Remove');

  }

}
