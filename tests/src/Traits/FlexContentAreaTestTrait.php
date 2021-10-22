<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Flex Content Area A & B field schema & validation.
 */
trait FlexContentAreaTestTrait {

  /**
   * Comprehensively verify Flex Content Area behavior.
   */
  public function verifyFlexContentArea() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $session = $this->getSession();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Flex Content Area';
    $block_name = $block_type . 'Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Open the media library.
    $session->wait(3000);
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    $page->pressButton('Add media');
    $this->assertNotEmpty($assert->waitForText('Add or select media'));
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));

    $page->fillField('edit-info-0-value', $block_name);
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

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
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

    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $this->assertNotEmpty($assert->waitForText('Flex Content Area 3'));
    // Expand the third fieldset.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    $fieldsets[2]->click();
    // Add only a headline to third instance.
    $three = 'field_block_fca[2][flex_content_area]';
    $page->fillField($three . '[headline]', 'Flex Content Area Headline 3');
    $page->pressButton('edit-submit');

    // CRUD: READ.
    $this->drupalGet('node/' . $flex_page);

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

    // Test rendering of YouTube video.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    $fieldsets[0]->click();
    $page->pressButton('image-0-media-library-remove-button-field_block_fca-0-flex_content_area');
    $this->assertNotEmpty($assert->waitForText('One media item remaining.'));
    $session->wait(3000);
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
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'src', "/media/oembed?url=https%3A//www.youtube.com/watch%3Fv%3DdQw4w9WgXcQ");
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'width', "100%");
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'height', "100%");

    // The outer iframe has a title attribute.
    // See https://github.austin.utexas.edu/eis1-wcs/utdk_profile/issues/1763.
    $assert->elementAttributeContains('css', '.ut-flex-content-area iframe', 'title', "YouTube content: Rick Astley - Never Gonna Give You Up (Official Music Video)");

    // The inner iframe has a title attribute.
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

  /**
   * Verify multiple FCAs work as designed.
   */
  public function verifyFlexContentAreaMultiple() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $session = $this->getSession();

    // CRUD: CREATE.
    $block_type = 'Flex Content Area';
    $block_name = $block_type . 'Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);
    $page->pressButton('Add another item');
    $session->wait(3000);
    $page->pressButton('Add another item');
    $session->wait(3000);
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    $page->fillField('edit-info-0-value', $block_name);
    $one = 'field_block_fca[0][flex_content_area]';
    $two = 'field_block_fca[1][flex_content_area]';
    $three = 'field_block_fca[2][flex_content_area]';
    $page->fillField($one . '[headline]', 'Flex Content Area Headline 1');
    // Leave the second FCA blank and put something in the third.
    $page->fillField($three . '[headline]', 'Flex Content Area Headline 3');

    $page->pressButton('edit-submit');
    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been created.');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    $assert->fieldValueEquals($two . '[headline]', 'Flex Content Area Headline 3');

    // Verify Flex Content Area items can be removed.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    $fieldsets[1]->click();
    // Clear out the data for item 2.
    $page->fillField($two . '[headline]', '');
    $page->pressButton('edit-submit');
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-flex-content-area details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Verify data for removed item is not present.
    $assert->pageTextNotContains('Flex Content Area Headline 3');

    // CRUD: DELETE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $page->clickLink('Delete');
    $page->pressButton('Delete');
    $this->drupalGet('admin/structure/block/block-content');
    $assert->pageTextNotContains($block_name);
  }

}
