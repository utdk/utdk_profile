<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Defines testing for Promo List widget.
 */
trait PromoListTestTrait {

  /**
   * Verify promo unit widget schema & output.
   */
  public function verifyPromoList() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('block/add/utexas_promo_list');
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

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

    // Verify the custom "Add promo list item" button works.
    $page->pressButton('Add promo list item');
    $assert->assertWaitOnAjaxRequest();

    // Verify multiple lists can be added.
    // Verify that multiple resource collections can be added.
    $page->pressButton('Add another item');
    $assert->assertWaitOnAjaxRequest();

    $fieldsets = $page->findAll('css', 'div.field--type-utexas-promo-list details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    $this->submitForm([
      'info[0][value]' => 'Promo List Test',
      'field_block_pl[0][headline]' => 'Promo List 1 Headline',
      'field_block_pl[0][promo_list_items][0][item][headline]' => 'List 1 item 1',
      'field_block_pl[0][promo_list_items][1][item][headline]' => 'List 1 item 2',
      'field_block_pl[0][promo_list_items][0][item][copy][value]' => 'Copy text for list 1 item 1',
      'field_block_pl[0][promo_list_items][0][item][link][url]' => 'https://promolist.test',
      'field_block_pl[1][headline]' => 'Promo List 2 Headline',
      'field_block_pl[1][promo_list_items][0][item][headline]' => 'List 2 item 1',
      'field_block_pl[1][promo_list_items][0][item][copy][value]' => 'Copy text for list 2 item 1',
      'field_block_pl[1][promo_list_items][0][item][link][url]' => '/node/1',
    ], 'Save');
    $assert->pageTextContains('Promo List Promo List Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'default',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementTextContains('css', '#block-promolisttest div div:nth-child(1) h3.ut-headline--underline', 'Promo List 1 Headline');
    $assert->elementTextContains('css', '#block-promolisttest div div:nth-child(2) h3.ut-headline--underline', 'Promo List 2 Headline');
    $assert->pageTextContains('List 1 item 2');
    $assert->pageTextContains('List 2 item 1');
    $assert->pageTextContains('Copy text for list 1 item 1');
    $assert->pageTextContains('Copy text for list 2 item 1');
    $assert->linkByHrefExists('https://promolist.test');
    $assert->linkByHrefExists('node/1');

    // Verify responsive image is present within the link.
    $assert->elementExists('css', '.ut-promo-list-wrapper .promo-list:nth-child(1) a picture source');
    $expected_path = 'utexas_image_style_64w_64h/public/image-test.png';
    $assert->elementAttributeContains('css', '.ut-promo-list-wrapper .promo-list:nth-child(1) a[href^="https://promolist.test"] picture img', 'src', $expected_path);

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
    $assert->elementExists('css', 'div.stacked-display > div.ut-promo-list-wrapper');

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/promolisttest/delete');
    $this->submitForm([], 'Remove');
  }

}
