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
    $this->drupalGet('block/add/utexas_promo_unit');

    // CRUD: CREATE.
    // Verify the custom "Add Promo Unit item" button works.
    $page->pressButton('Add Promo Unit item');
    $assert->waitForText('Promo Unit item 2');
    $page->pressButton('Show row weights');

    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-unit details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Verify widget field schema.
    $page->pressButton('Add media');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Add or select media');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $assert->assertWaitOnAjaxRequest();

    $basic_page_id = $this->createBasicPage();
    $this->submitForm([
      'info[0][value]' => 'Promo Unit Test',
      'field_block_pu[0][headline]' => 'Promo Unit Container Headline',
      'field_block_pu[0][promo_unit_items][items][0][details][item][headline]' => 'Promo Unit 1 Headline',
      'field_block_pu[0][promo_unit_items][items][0][details][item][copy][value]' => 'Promo Unit 1 Copy',
      'field_block_pu[0][promo_unit_items][items][0][details][item][link][uri]' => 'https://promounit.test',
      'field_block_pu[0][promo_unit_items][items][0][details][item][link][title]' => 'Promo Unit External Link',
      'field_block_pu[0][promo_unit_items][items][0][details][item][link][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_pu[0][promo_unit_items][items][0][details][item][link][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_pu[0][promo_unit_items][items][1][details][item][headline]' => 'Promo Unit 2 Headline',
      'field_block_pu[0][promo_unit_items][items][1][details][item][copy][value]' => 'Promo Unit 2 Copy',
      'field_block_pu[0][promo_unit_items][items][1][details][item][link][uri]' => '/node/' . $basic_page_id,
      'field_block_pu[0][promo_unit_items][items][1][details][item][link][title]' => 'Promo Unit Internal Link',
      'field_block_pu[0][promo_unit_items][items][1][details][item][link][options][attributes][class]' => 'ut-cta-link--lock',
      'field_block_pu[0][promo_unit_items][items][0][weight]' => 1,
      'field_block_pu[0][promo_unit_items][items][1][weight]' => 0,
    ], 'Save');
    $assert->pageTextContains('Promo Unit Promo Unit Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'default',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    // CRUD: READ.
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementTextContains('css', 'h3.ut-headline--underline', 'Promo Unit Container Headline');
    // User-supplied weighting of resource items is respected.
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(3) h3.ut-headline', 'Promo Unit 1 Headline');
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(2) h3.ut-headline', 'Promo Unit 2 Headline');
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(3)', 'Promo Unit 1 Copy');
    $assert->elementTextContains('css', '.utexas-promo-unit:nth-child(2)', 'Promo Unit 2 Copy');
    $assert->linkByHrefExists('https://promounit.test');
    $assert->linkByHrefExists('test-basic-page');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');
    // Verify CTA not tabbable when headline and link present.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'tabindex', '-1');
    $assert->elementExists('css', '.ut-cta-link--lock');
    // Verify responsive image is present.
    $assert->elementExists('css', '.utexas-promo-unit:nth-child(3) picture source');
    // Verify image is not a link after a11y changes.
    $assert->elementNotExists('css', '.utexas-promo-unit:nth-child(3) a picture source');
    // Verify expected image.
    $expected_path = 'utexas_image_style_176w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit:nth-child(3) picture img', 'src', $expected_path);

    // Set display to "Portrait".
    $this->drupalGet('admin/structure/block/manage/promounittest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_unit_2',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $expected_path = 'utexas_image_style_120w_150h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);

    // Set display to "Square".
    $this->drupalGet('admin/structure/block/manage/promounittest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_unit_3',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $expected_path = 'utexas_image_style_112w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);

    // Set display to "Landscape Stacked".
    $this->drupalGet('admin/structure/block/manage/promounittest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_unit_4',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $expected_path = 'utexas_image_style_176w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);
    $assert->elementExists('css', 'div.stacked-display div.utexas-promo-unit');

    // Set display to "Portrait Stacked".
    $this->drupalGet('admin/structure/block/manage/promounittest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_unit_5',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $expected_path = 'utexas_image_style_120w_150h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);
    $assert->elementExists('css', 'div.stacked-display div.utexas-promo-unit');

    // Set display to "Square Stacked".
    $this->drupalGet('admin/structure/block/manage/promounittest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_unit_6',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $expected_path = 'utexas_image_style_112w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', '.utexas-promo-unit picture img', 'src', $expected_path);
    $assert->elementExists('css', 'div.stacked-display div.utexas-promo-unit');

    // Reset block weighting system.
    $this->drupalGet('/admin/structure/block/block-content');
    $checkbox_selector = '.views-field-operations li.edit';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $page->pressButton('Hide row weights');

    // CRUD: UPDATE.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Promo Unit Test')->click();
    // Add a third Promo Unit items.
    $page->pressButton('Add Promo Unit item');
    $assert->assertWaitOnAjaxRequest();
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-unit details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    // Clear out the data for item 2; add item 3.
    $page->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][headline]', '');
    $page->pressButton('image-0-media-library-remove-button-field_block_pu-0-promo_unit_items-items-1-details-item');
    $page->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][copy][value]', '');
    $page->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][link][uri]', '');
    $page->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][link][title]', '');
    $page->fillField('field_block_pu[0][promo_unit_items][items][1][details][item][link][options][attributes][class]', '0');
    $page->uncheckField('field_block_pu[0][promo_unit_items][items][1][details][item][link][options][attributes][target][_blank]');
    $page->fillField('field_block_pu[0][promo_unit_items][items][2][details][item][headline]', 'Promo Unit 3 Headline');
    $page->pressButton('edit-submit');

    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Promo Unit Test')->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-unit details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Verify data for item entered in slot 3 is deposited in the empty slot 2.
    $assert->fieldValueEquals('field_block_pu[0][promo_unit_items][items][1][details][item][headline]', 'Promo Unit 3 Headline');
    // Verify data for removed item is not present.
    $assert->pageTextNotContains('Promo Unit 1 Headline');

    // CRUD: DELETE.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Promo Unit Test')->click();
    $page->clickLink('Delete');
    $page->pressButton('Delete');
    $this->drupalGet('admin/structure/block/block-content');
    $assert->pageTextNotContains('Promo Unit Test');

    // Remove test node.
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple([$basic_page_id]);
    $storage_handler->delete($entities);
  }

}
