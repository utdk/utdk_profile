<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Defines testing for Promo List widget.
 */
trait PromoListTestTrait {

  /**
   * Verify Promo List widget schema & output.
   */
  public function verifyPromoList() {
    // Enlarge the viewport so that all Promo Lists are clickable.
    $this->getSession()->resizeWindow(1200, 3000);

    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('block/add/utexas_promo_list');
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    // Open the media library.
    $page->pressButton('Add media');
    $this->assertNotEmpty($assert->waitForText('Add or select media'));
    $assert->pageTextContains('Image 1');

    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();

    // Insert the media item & verify the media library interface closes.
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));

    // Verify the custom "Add Promo List item" button works.
    $page->pressButton('Add Promo List item');
    $this->assertNotEmpty($assert->waitForText('Promo List item 2'));

    // Multiple Promo List collections can be added.
    $page->pressButton('Add another item');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '[data-drupal-selector="edit-field-block-pl-1-promo-list-items-items"]'));

    // Multiple list items can be added.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    $page->pressButton('Show row weights');
    $basic_page_id = $this->createBasicPage();

    $page->fillField('edit-info-0-value', 'Promo List Test');
    $page->fillField('field_block_pl[0][headline]', 'Promo List 1 Headline');
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][headline]', 'List 1 item 1');
    $page->fillField('field_block_pl[0][promo_list_items][items][1][details][item][headline]', 'List 1 item 2');
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][copy][value]', 'Copy text for list 1 item 1');
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][link][uri]', 'https://promolist.test');
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][link][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][link][options][attributes][class]', 'ut-cta-link--external');

    // Use weighting fields to reverse the order of Promo List items 1 & 2.
    $page->fillField('field_block_pl[0][promo_list_items][items][0][weight]', '1');
    $page->fillField('field_block_pl[0][promo_list_items][items][1][weight]', '0');

    // Populate Promo List collection #2.
    $page->fillField('field_block_pl[1][headline]', 'Promo List 2 Headline');
    $page->fillField('field_block_pl[1][promo_list_items][items][0][details][item][headline]', 'List 2 item 1');
    $page->fillField('field_block_pl[1][promo_list_items][items][0][details][item][copy][value]', 'Copy text for list 2 item 1');
    $page->fillField('field_block_pl[1][promo_list_items][items][0][details][item][link][uri]', '/node/' . $basic_page_id);
    $page->fillField('field_block_pl[1][promo_list_items][items][0][details][item][link][options][attributes][class]', 'ut-cta-link--lock');
    $page->pressButton('edit-submit');
    $assert->pageTextContains('Promo List Promo List Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'default',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');

    // Promo List items 1 & 2 have been reordered.
    $assert->elementTextContains('css', '#block-promolisttest div.promo-list:nth-child(1) h3.ut-headline', 'List 1 item 2');
    $assert->elementTextContains('css', '#block-promolisttest div.promo-list:nth-child(2) h3.ut-headline', 'List 1 item 1');

    // Other input is present.
    $assert->elementTextContains('css', '#block-promolisttest div div:nth-child(1) h3.ut-headline--underline', 'Promo List 1 Headline');
    $assert->elementTextContains('css', '#block-promolisttest div div:nth-child(2) h3.ut-headline--underline', 'Promo List 2 Headline');
    $assert->pageTextContains('Copy text for list 1 item 1');
    $assert->pageTextContains('Copy text for list 2 item 1');
    $assert->linkByHrefExists('https://promolist.test');
    $assert->linkByHrefExists('test-basic-page');

    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');
    $assert->elementExists('css', '.ut-cta-link--lock');

    // Verify responsive image is present.
    $assert->elementExists('css', '.ut-promo-list-wrapper .promo-list:nth-child(2) picture source');
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.ut-promo-list-wrapper .promo-list:nth-child(2) a picture source');
    // Verify expected image.
    $expected_path = 'utexas_image_style_64w_64h/public/image-test.png';
    $assert->elementAttributeContains('css', '.ut-promo-list-wrapper .promo-list:nth-child(2) picture img', 'src', $expected_path);

    // Set display to "Responsive".
    $this->drupalGet('admin/structure/block/manage/promolisttest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_list_2',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', 'div.ut-promo-list-wrapper.two-column-responsive');

    // Set display to "Two Columns".
    $this->drupalGet('admin/structure/block/manage/promolisttest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_list_3',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', 'div.ut-promo-list-wrapper.two-side-by-side');

    // Set display to "Stacked".
    $this->drupalGet('admin/structure/block/manage/promolisttest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_list_4',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', 'div.stacked-display > div.utexas-promo-list-container > div.ut-promo-list-wrapper');

    // Reset block weighting system.
    $this->drupalGet('/admin/structure/block/block-content');
    $checkbox_selector = '.views-field-operations li.edit';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $page->pressButton('Hide row weights');

    // CRUD: UPDATE.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Promo List Test')->click();
    // Add a third item.
    $page->pressButton('Add Promo List item');
    $this->assertNotEmpty($assert->waitForText('Promo List item 3'));
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    // Clear out the data for item 2; add item 3.
    $page->fillField('field_block_pl[0][promo_list_items][items][1][details][item][headline]', '');
    $page->pressButton('image-0-media-library-remove-button-field_block_pl-0-promo_list_items-items-1-details-item');
    $page->fillField('field_block_pl[0][promo_list_items][items][1][details][item][copy][value]', '');
    $page->fillField('field_block_pl[0][promo_list_items][items][1][details][item][link][uri]', '');
    $page->fillField('field_block_pl[0][promo_list_items][items][1][details][item][link][options][attributes][class]', '0');
    $page->uncheckField('field_block_pl[0][promo_list_items][items][1][details][item][link][options][attributes][target][_blank]');
    $page->fillField('field_block_pl[0][promo_list_items][items][2][details][item][headline]', 'Promo List 3 Headline');
    $page->pressButton('edit-submit');
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Promo List Test')->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Verify data for item entered in slot 3 is deposited in the empty slot 2.
    $assert->fieldValueEquals('field_block_pl[0][promo_list_items][items][1][details][item][headline]', 'Promo List 3 Headline');
    // Verify data for removed item is not present.
    $assert->pageTextNotContains('Promo List 1 Headline');

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/promolisttest/delete');
    $this->submitForm([], 'Remove');
    // Remove test page.
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple([$basic_page_id]);
    $storage_handler->delete($entities);
    // Reset to the standard window width.
    $this->getSession()->resizeWindow(900, 2000);
  }

}
