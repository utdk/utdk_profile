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
    $session = $this->getSession();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Photo Content Area';
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
      'info[0][value]' => $block_name,
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

    // CRUD: UPDATE.
    // Verify stacked display formatter adding class to markup.
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_photo_content_area_2',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('The layout override has been saved.');
    // Verify page output.
    $assert->elementExists('css', 'div.stacked-display div.ut-photo-content-area');

    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    // Remove data for the second link
    // (#1121: Verify links can be removed without loss of data.)
    $page->fillField('field_block_pca[0][links][1][uri]', '');
    $page->fillField('field_block_pca[0][links][1][title]', '');
    $page->pressButton('edit-submit');

    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $assert->fieldValueEquals('field_block_pca[0][links][1][title]', 'Photo Content Area Third Link');
    $assert->fieldValueEquals('field_block_pca[0][links][1][uri]', 'https://third.test');
    // Verify data for removed link is not present.
    $assert->pageTextNotContains('https://second.test');

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
