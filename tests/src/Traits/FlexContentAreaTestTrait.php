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

    // CRUD: CREATE.
    $this->drupalGet('block/add/utexas_flex_content_area');
    // Expand the collapsed fieldset for populating data.
    $page->find('css', 'div.field--type-utexas-flex-content-area details')->click();

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

    $page->fillField('edit-info-0-value', 'Flex Content Area Test');
    $one = 'field_block_fca[0][flex_content_area]';
    $page->fillField($one . '[headline]', 'Flex Content Area Headline 1');
    $page->fillField($one . '[copy][value]', 'Flex Content Area Copy');
    $page->fillField($one . '[links][0][title]', 'Flex Content Area External Link');
    $page->fillField($one . '[links][0][uri]', 'https://utexas.edu');
    $page->fillField($one . '[links][0][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $page->fillField($one . '[links][0][options][attributes][class]', 'ut-cta-link--lock');
    // Add slots for two more links on Flex Content Area instance 1.
    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_fca[0][flex_content_area][links][1][uri]',
    ]));
    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_fca[0][flex_content_area][links][2][uri]',
    ]));
    $page->fillField($one . '[links][1][title]', 'Flex Content Area Second Link');
    $page->fillField($one . '[links][1][uri]', 'https://second.test');
    $page->fillField($one . '[links][2][title]', 'Flex Content Area Third Link');
    $page->fillField($one . '[links][2][uri]', 'https://third.test');
    $page->fillField($one . '[cta_wrapper][link][uri]', 'https://utexas.edu');
    $page->fillField($one . '[cta_wrapper][link][title]', 'Flex Content Area Call to Action');
    $page->fillField($one . '[cta_wrapper][link][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $page->fillField($one . '[cta_wrapper][link][options][attributes][class]', 'ut-cta-link--external');
    $page->pressButton('edit-submit');
    $assert->pageTextContains('Flex Content Area Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    // CRUD: UPDATE.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Flex Content Area Test')->click();
    // Add two more Flex Content Area instances.
    $page->pressButton('Add another item');
    $this->assertNotEmpty($assert->waitForText('Flex Content Area 3'));
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    // Expand the second fieldset.
    $fieldsets[1]->click();
    $two = 'field_block_fca[1][flex_content_area]';
    $page->fillField($two . '[headline]', 'Flex Content Area Headline 2');
    $page->fillField($two . '[copy][value]', 'Flex Content Area Copy 2');
    $page->fillField($two . '[links][0][title]', 'Flex Content Area External Link 2');
    $page->fillField($two . '[links][0][uri]', 'https://utexas.edu');
    $page->fillField($two . '[cta_wrapper][link][uri]', 'https://utexas.edu');
    $page->fillField($two . '[cta_wrapper][link][title]', 'Flex Content Area Call to Action 2');
    $page->pressButton('edit-submit');

    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Flex Content Area Test')->click();
    $this->assertNotEmpty($assert->waitForText('Flex Content Area 3'));
    // Expand the third fieldset.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    $fieldsets[2]->click();
    // Add only a headline to third instance.
    $three = 'field_block_fca[2][flex_content_area]';
    $page->fillField($three . '[headline]', 'Flex Content Area Headline 3');
    $page->pressButton('edit-submit');

    // CRUD: READ.
    $this->drupalGet('<front>');

    // Flex Content Area instance 1 is rendered.
    $assert->elementTextContains('css', 'h3.ut-headline', 'Flex Content Area Headline');
    $assert->pageTextContains('Flex Content Area Copy');
    $assert->linkByHrefExists('https://utexas.edu');
    $assert->linkByHrefExists('https://second.test');
    $assert->linkByHrefExists('https://third.test');
    $assert->elementTextContains('css', 'a.ut-btn', 'Flex Content Area Call to Action');
    // Verify link exists with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');
    // Verify CTA not tabbable when headline and link present.
    $assert->elementAttributeContains('css', 'div.content-wrapper > a.ut-cta-link--external', 'tabindex', '-1');
    $assert->elementExists('css', '.ut-cta-link--lock');
    // Verify responsive and expected image is present.
    $expected_path = 'utexas_image_style_340w_227h/public/image-test.png';
    $assert->elementAttributeContains('css', '.ut-flex-content-area .image-wrapper picture img', 'src', $expected_path);
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.ut-flex-content-area .image-wrapper a picture source');

    // Flex Content Area instance 2 is rendered.
    $assert->elementTextContains('css', '.ut-flex-content-area:nth-child(2) h3.ut-headline', 'Flex Content Area Headline 2');
    $assert->pageTextContains('Flex Content Area Copy 2');
    $assert->linkExists('Flex Content Area External Link 2');
    $assert->elementTextContains('css', '.ut-flex-content-area:nth-child(2) a.ut-btn', 'Flex Content Area Call to Action 2');

    // Empty Flex Content Area instance 3 elements do not render.
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(3) a.ut-btn');
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(3) .ut-copy');
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(3) .link-list');
    $assert->elementNotExists('css', '.ut-flex-content-area:nth-child(3) .image-wrapper');

    // CRUD: UPDATE.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Flex Content Area Test')->click();
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    $fieldsets[0]->click();

    // Remove data for the second link
    // (#1121: Verify links can be removed without loss of data.)
    $page->fillField('field_block_fca[0][flex_content_area][links][1][uri]', '');
    $page->fillField('field_block_fca[0][flex_content_area][links][1][title]', '');
    $page->pressButton('edit-submit');

    // Return to the block.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Flex Content Area Test')->click();
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    $fieldsets[0]->click();
    // Confirm second link has data from third link previously created.
    $assert->fieldValueEquals('field_block_fca[0][flex_content_area][links][1][title]', 'Flex Content Area Third Link');
    $assert->fieldValueEquals('field_block_fca[0][flex_content_area][links][1][uri]', 'https://third.test');
    // Verify data for removed link is not present.
    $assert->pageTextNotContains('https://second.test');

    // Verify Flex Content Area items can be removed.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Flex Content Area Test')->click();
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    $fieldsets[1]->click();
    $fieldsets[2]->click();
    // Clear out the data for item 2.
    $page->fillField('field_block_fca[1][flex_content_area][headline]', '');
    $page->fillField('field_block_fca[1][flex_content_area][copy][value]', '');
    $page->fillField('field_block_fca[1][flex_content_area][links][0][uri]', '');
    $page->fillField('field_block_fca[1][flex_content_area][links][0][title]', '');
    $page->fillField('field_block_fca[1][flex_content_area][cta_wrapper][link][uri]', '');
    $page->fillField('field_block_fca[1][flex_content_area][cta_wrapper][link][title]', '');
    $page->pressButton('edit-submit');
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Flex Content Area Test')->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Verify data for item entered in slot 3 is deposited in the empty slot 2.
    $assert->fieldValueEquals('field_block_fca[1][flex_content_area][headline]', 'Flex Content Area Headline 3');
    // Verify data for removed item is not present.
    $assert->pageTextNotContains('Flex Content Area Headline 2');

    // CRUD: DELETE.
    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Flex Content Area Test')->click();
    $page->clickLink('Delete');
    $page->pressButton('Delete');
    $this->drupalGet('admin/structure/block/block-content');
    $assert->pageTextNotContains('Flex Content Area Test');

    // Test rendering of YouTube video.
    $this->drupalGet('block/add/utexas_flex_content_area');
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    // Verify widget field schema.
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

    $this->submitForm([
      'info[0][value]' => 'Flex Content Area Video Test',
      'field_block_fca[0][flex_content_area][headline]' => 'Flex Content Area Headline',
      'field_block_fca[0][flex_content_area][copy][value]' => 'Flex Content Area Copy',
      'field_block_fca[0][flex_content_area][links][0][uri]' => 'https://utexas.edu',
      'field_block_fca[0][flex_content_area][links][0][title]' => 'Flex Content Area External Link',
      'field_block_fca[0][flex_content_area][cta_wrapper][link][uri]' => 'https://utexas.edu',
      'field_block_fca[0][flex_content_area][cta_wrapper][link][title]' => 'Flex Content Area Call to Action',
    ], 'Save');
    $assert->pageTextContains('Flex Content Area Flex Content Area Video Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'src', "/media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3DdQw4w9WgXcQ");
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'width', "100%");
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'height', "100%");

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/flexcontentareavideotest/delete');
    $this->submitForm([], 'Remove');
  }

}
