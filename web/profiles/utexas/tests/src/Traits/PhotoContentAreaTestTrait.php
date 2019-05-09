<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Photo Content Area field schema, validation, & output.
 */
trait PhotoContentAreaTestTrait {

  /**
   * Test schema.
   */
  public function verifyPhotoContentArea() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('block/add/utexas_photo_content_area');

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

    $this->submitForm([
      'info[0][value]' => 'Photo Content Area Test',
      'field_block_pca[0][photo_credit]' => 'Photo Content Area Photo Credit',
      'field_block_pca[0][headline]' => 'Photo Content Area Headline',
      'field_block_pca[0][copy][value]' => 'Photo Content Area Copy',
      'field_block_pca[0][links][0][url]' => 'https://photocontentarea.test',
      'field_block_pca[0][links][0][title]' => 'Photo Content Area Link',
    ], 'Save');
    $assert->pageTextContains('Photo Content Area Photo Content Area Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementTextContains('css', 'div.caption span', 'Photo Content Area Photo Credit');
    $assert->elementTextContains('css', 'h2.ut-headline', 'Photo Content Area Headline');
    $assert->pageTextContains('Photo Content Area Copy');
    $assert->linkByHrefExists('https://photocontentarea.test');
    // Verify responsive image is present.
    $expected_path = 'utexas_image_style_450w_600h/public/image-test';
    $assert->elementAttributeContains('css', '.photo-wrapper picture img', 'src', $expected_path);

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/photocontentareatest/delete');
    $this->submitForm([], 'Remove');
  }

}
