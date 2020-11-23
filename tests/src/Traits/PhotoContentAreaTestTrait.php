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

    // CRUD: CREATE.
    $this->drupalGet('block/add/utexas_photo_content_area');

    // Verify widget field schema.
    $page->pressButton('Add media');
    $this->assertNotEmpty($assert->waitForText('Add or select media'));
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));

    // Add two more link slots.
    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_pca[0][links][1][uri]',
    ]));
    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_pca[0][links][2][uri]',
    ]));

    $this->submitForm([
      'info[0][value]' => 'Photo Content Area Test',
      'field_block_pca[0][photo_credit]' => 'Photo Content Area Photo Credit',
      'field_block_pca[0][headline]' => 'Photo Content Area Headline',
      'field_block_pca[0][copy][value]' => 'Photo Content Area Copy',
      'field_block_pca[0][links][0][uri]' => 'https://photocontentarea.test',
      'field_block_pca[0][links][0][title]' => 'Photo Content Area Link',
      'field_block_pca[0][links][0][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_pca[0][links][0][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_pca[0][links][1][uri]' => 'https://second.test',
      'field_block_pca[0][links][1][title]' => 'Photo Content Area Second Link',
      'field_block_pca[0][links][2][uri]' => 'https://third.test',
      'field_block_pca[0][links][2][title]' => 'Photo Content Area Third Link',
    ], 'Save');
    $assert->pageTextContains('Photo Content Area Photo Content Area Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    // CRUD: READ.
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementTextContains('css', 'div.caption span', 'Photo Content Area Photo Credit');
    $assert->elementTextContains('css', '.ut-photo-content-area h2.ut-headline', 'Photo Content Area Headline');
    $assert->pageTextContains('Photo Content Area Copy');
    $assert->linkByHrefExists('https://photocontentarea.test');
    $assert->linkByHrefExists('https://second.test');
    $assert->linkByHrefExists('https://third.test');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');
    // Verify responsive image is present.
    $expected_path = 'utexas_image_style_450w_600h/public/image-test';
    $assert->elementAttributeContains('css', '.photo-wrapper picture img', 'src', $expected_path);
    // Verify stacked display formatter adding class to markup.
    $this->drupalGet('admin/structure/block/manage/photocontentareatest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_photo_content_area_2',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', 'div.stacked-display div.ut-photo-content-area');

    // CRUD: UPDATE.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Photo Content Area Test')->click();
    // Remove data for the second link
    // (#1121: Verify links can be removed without loss of data.)
    $page->fillField('field_block_pca[0][links][1][uri]', '');
    $page->fillField('field_block_pca[0][links][1][title]', '');
    $page->pressButton('edit-submit');

    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Photo Content Area Test')->click();
    $assert->fieldValueEquals('field_block_pca[0][links][1][title]', 'Photo Content Area Third Link');
    $assert->fieldValueEquals('field_block_pca[0][links][1][uri]', 'https://third.test');
    // Verify data for removed link is not present.
    $assert->pageTextNotContains('https://second.test');

    // CRUD: DELETE.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Photo Content Area Test')->click();
    $page->clickLink('Delete');
    $page->pressButton('Delete');
    $this->drupalGet('admin/structure/block/block-content');
    $assert->pageTextNotContains('Photo Content Area Test');
  }

}
