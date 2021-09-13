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
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $session = $this->getSession();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Promo List';
    $block_name = $block_type . 'Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Open the media library.
    $session->wait(3000);
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    $page->pressButton('Add media');
    $session->wait(3000);
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
    $session->wait(3000);
    $page->pressButton('Show row weights');

    $page->fillField('edit-info-0-value', $block_name);
    $page->fillField('field_block_pl[0][headline]', 'Promo List 1 Headline');
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][headline]', 'List 1 item 1');
    $page->fillField('field_block_pl[0][promo_list_items][items][1][details][item][headline]', 'List 1 item 2');
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][copy][value]', 'Copy text for list 1 item 1');
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][link][uri]', '/sites/default/files/file%20with%20spaces.pdf');
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][link][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][link][options][attributes][class]', 'ut-cta-link--external');

    // Use weighting fields to reverse the order of Promo List items 1 & 2.
    $page->fillField('field_block_pl[0][promo_list_items][items][0][weight]', '1');
    $page->fillField('field_block_pl[0][promo_list_items][items][1][weight]', '0');

    // Populate Promo List collection #2.
    $page->fillField('field_block_pl[1][headline]', 'Promo List 2 Headline');
    $page->fillField('field_block_pl[1][promo_list_items][items][0][details][item][headline]', 'List 2 item 1');
    $page->fillField('field_block_pl[1][promo_list_items][items][0][details][item][copy][value]', 'Copy text for list 2 item 1');
    $page->fillField('field_block_pl[1][promo_list_items][items][0][details][item][link][uri]', '/node/' . $flex_page);
    $page->fillField('field_block_pl[1][promo_list_items][items][0][details][item][link][options][attributes][class]', 'ut-cta-link--lock');
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

    // Promo List items 1 & 2 have been reordered.
    $assert->elementTextContains('css', '.block-block-content div.promo-list:nth-child(1) h3.ut-headline', 'List 1 item 2');
    $assert->elementTextContains('css', '.block-block-content div.promo-list:nth-child(2) h3.ut-headline', 'List 1 item 1');

    // Other input is present.
    $assert->elementTextContains('css', '.block-block-content div div:nth-child(1) h3.ut-headline--underline', 'Promo List 1 Headline');
    $assert->elementTextContains('css', '.block-block-content div div:nth-child(2) h3.ut-headline--underline', 'Promo List 2 Headline');
    $assert->pageTextContains('Copy text for list 1 item 1');
    $assert->pageTextContains('Copy text for list 2 item 1');
    // Verify that double-encoding does not occur.
    $assert->linkByHrefExists('/sites/default/files/file%20with%20spaces.pdf');
    $assert->linkByHrefExists('test-flex-page');

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

    // CRUD: UPDATE.
    // Set display to "Responsive".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_promo_list_2',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $assert->elementExists('css', 'div.ut-promo-list-wrapper.two-column-responsive');

    // Set display to "Two Columns".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_promo_list_3',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $assert->elementExists('css', 'div.ut-promo-list-wrapper.two-side-by-side');

    // Set display to "Stacked".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_promo_list_4',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $assert->elementExists('css', 'div.stacked-display > div.utexas-promo-list-container > div.ut-promo-list-wrapper');

    // Re-set row weight for subsequent tests.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $page->pressButton('Hide row weights');

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
   * Verify multiple Promo List items work as expected.
   */
  public function verifyPromoListMultiple() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $block_type = 'Promo List';
    $block_name = $block_type . 'Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Verify the custom "Add Promo List item" button works.
    $page->pressButton('Add Promo List item');
    $this->assertNotEmpty($assert->waitForText('Promo List item 2'));
    $page->pressButton('Add Promo List item');
    $this->assertNotEmpty($assert->waitForText('Promo List item 3'));

    // Multiple list items can be added.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    $page->fillField('edit-info-0-value', $block_name);
    $page->fillField('field_block_pl[0][headline]', 'Promo List 1 Headline');
    $page->fillField('field_block_pl[0][promo_list_items][items][0][details][item][headline]', 'List 1 item 1');
    $page->fillField('field_block_pl[0][promo_list_items][items][2][details][item][headline]', 'List 1 item 3');
    $page->pressButton('edit-submit');
    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been created.');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Verify data for item entered in slot 3 is deposited in the empty slot 2.
    $assert->fieldValueEquals('field_block_pl[0][promo_list_items][items][1][details][item][headline]', 'List 1 item 3');

    // Clear out the data for item 2.
    $page->fillField('field_block_pl[0][promo_list_items][items][1][details][item][headline]', '');
    $page->pressButton('edit-submit');

    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    $assert->pageTextNotContains('List 1 item 3');

    // CRUD: DELETE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $page->clickLink('Delete');
    $page->pressButton('Delete');
    $this->drupalGet('admin/structure/block/block-content');
    $assert->pageTextNotContains($block_name);

  }

}
