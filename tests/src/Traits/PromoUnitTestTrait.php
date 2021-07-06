<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Defines testing for Promo Unit widget.
 */
trait PromoUnitTestTrait {

  /**
   * Verify promo unit widget schema & output.
   */
  public function verifyPromoUnit() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $session = $this->getSession();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Promo Unit';
    $block_name = $block_type . 'Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Verify the custom "Add Promo Unit item" button works.
    $page->pressButton('Add Promo Unit item');
    $this->assertNotEmpty($assert->waitForText('Promo Unit item 2'));
    $page->pressButton('Show row weights');

    // Expand the fieldsets.
    $session->wait(3000);
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-unit details');
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

    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_pu[0][headline]' => 'Promo Unit Container Headline',
      'field_block_pu[0][promo_unit_items][items][0][details][item][headline]' => 'Promo Unit 1 Headline',
      'field_block_pu[0][promo_unit_items][items][0][details][item][copy][value]' => 'Promo Unit 1 Copy',
      'field_block_pu[0][promo_unit_items][items][0][details][item][link][uri]' => 'https://promounit.test',
      'field_block_pu[0][promo_unit_items][items][0][details][item][link][title]' => 'Promo Unit External Link',
      'field_block_pu[0][promo_unit_items][items][0][details][item][link][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_pu[0][promo_unit_items][items][0][details][item][link][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_pu[0][promo_unit_items][items][1][details][item][headline]' => 'Promo Unit 2 Headline',
      'field_block_pu[0][promo_unit_items][items][1][details][item][copy][value]' => 'Promo Unit 2 Copy',
      'field_block_pu[0][promo_unit_items][items][1][details][item][link][uri]' => '/node/' . $flex_page,
      'field_block_pu[0][promo_unit_items][items][1][details][item][link][title]' => 'Promo Unit Internal Link',
      'field_block_pu[0][promo_unit_items][items][1][details][item][link][options][attributes][class]' => 'ut-cta-link--lock',
      'field_block_pu[0][promo_unit_items][items][0][weight]' => 1,
      'field_block_pu[0][promo_unit_items][items][1][weight]' => 0,
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

    // CRUD: READ.
    // Verify page output.
    $assert->elementTextContains('css', 'h3.ut-headline--underline', 'Promo Unit Container Headline');
    // User-supplied weighting of resource items is respected.
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(2) h3.ut-headline', 'Promo Unit 1 Headline');
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(1) h3.ut-headline', 'Promo Unit 2 Headline');
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(2)', 'Promo Unit 1 Copy');
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(1)', 'Promo Unit 2 Copy');
    $assert->linkByHrefExists('https://promounit.test');
    $assert->linkByHrefExists('test-flex-page');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');
    // Verify CTA not tabbable when headline and link present.
    $assert->elementAttributeContains('css', 'div > a.ut-cta-link--external', 'tabindex', '-1');
    $assert->elementExists('css', '.ut-cta-link--lock');
    // Verify responsive image is present.
    $assert->elementExists('css', '.utexas-promo-unit:nth-child(2) picture source');
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.utexas-promo-unit:nth-child(2) a picture source');
    // Verify expected image.
    $expected_path = 'utexas_image_style_176w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit:nth-child(2) picture img', 'src', $expected_path);

    // CRUD: UPDATE.
    // Set display to "Responsive".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_promo_unit_2',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $expected_path = 'utexas_image_style_120w_150h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);

    // Set display to "Square".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_promo_unit_3',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $expected_path = 'utexas_image_style_112w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);

    // Set display to "Landscape Stacked".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_promo_unit_4',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $expected_path = 'utexas_image_style_176w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);
    $assert->elementExists('css', 'div.stacked-display div.utexas-promo-unit');

    // Set display to "Portrait Stacked".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_promo_unit_5',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $expected_path = 'utexas_image_style_120w_150h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);
    $assert->elementExists('css', 'div.stacked-display div.utexas-promo-unit');

    // Set display to "Square Stacked".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $this->submitForm([
      'settings[view_mode]' => 'utexas_promo_unit_6',
    ], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $expected_path = 'utexas_image_style_112w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);
    $assert->elementExists('css', 'div.stacked-display div.utexas-promo-unit');

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
   * Verify multiple promo unit items work as designed.
   */
  public function verifyPromoUnitMultiple() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $session = $this->getSession();

    // CRUD: CREATE.
    $block_type = 'Promo Unit';
    $block_name = $block_type . 'Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Verify the custom "Add Promo Unit item" button works.
    $page->pressButton('Add Promo Unit item');
    $this->assertNotEmpty($assert->waitForText('Promo Unit item 2'));
    $page->pressButton('Add Promo Unit item');
    $this->assertNotEmpty($assert->waitForText('Promo Unit item 3'));

    // Expand the fieldsets.
    $session->wait(3000);
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-unit details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_pu[0][headline]' => 'Promo Unit Container Headline',
      'field_block_pu[0][promo_unit_items][items][0][details][item][headline]' => 'Promo Unit 1 Headline',
      'field_block_pu[0][promo_unit_items][items][2][details][item][headline]' => 'Promo Unit 3 Headline',
    ], 'Save');
    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been created.');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-unit details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    $assert->fieldValueEquals('field_block_pu[0][promo_unit_items][items][1][details][item][headline]', 'Promo Unit 3 Headline');

    // Clear out the data for item 2.
    $page->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][headline]', '');
    $page->pressButton('edit-submit');

    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-unit details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    $assert->pageTextNotContains('Promo Unit 3 Headline');

    // CRUD: DELETE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    $page->clickLink('Delete');
    $page->pressButton('Delete');
    $this->drupalGet('admin/structure/block/block-content');
    $assert->pageTextNotContains($block_name);
  }

}
