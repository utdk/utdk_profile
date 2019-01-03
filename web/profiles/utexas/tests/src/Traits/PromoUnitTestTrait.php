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

    // Verify widget field schema.
    $this->clickLink('Add media');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Media library');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonpane')->pressButton('Select media');
    $assert->assertWaitOnAjaxRequest();

    $this->submitForm([
      'info[0][value]' => 'Promo Unit Test',
      'field_block_pu[0][headline]' => 'Promo Unit Container Headline',
      'field_block_pu[0][link-fieldset][promo_unit_items][0][item][headline]' => 'Promo Unit 1 Headline',
      'field_block_pu[0][link-fieldset][promo_unit_items][0][item][copy][value]' => 'Promo Unit 1 Copy',
      'field_block_pu[0][link-fieldset][promo_unit_items][0][item][link][url]' => 'https://promounit.test',
      'field_block_pu[0][link-fieldset][promo_unit_items][0][item][link][title]' => 'Promo Unit External Link',
    ], 'Save');
    $assert->pageTextContains('Promo Unit Promo Unit Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'default',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementTextContains('css', 'h3.ut-headline--underline', 'Promo Unit Container Headline');
    $assert->elementTextContains('css', 'h3.ut-headline', 'Promo Unit 1 Headline');
    $assert->pageTextContains('Promo Unit 1 Copy');
    $assert->linkByHrefExists('https://promounit.test');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'a picture source');
    $expected_path = 'utexas_image_style_176w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="https://promounit.test"] picture img', 'src', $expected_path);

    // Set display to "Portrait".
    $this->drupalGet('admin/structure/block/manage/promounittest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_unit_2',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $expected_path = 'utexas_image_style_120w_150h/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="https://promounit.test"] picture img', 'src', $expected_path);

    // Set display to "Square".
    $this->drupalGet('admin/structure/block/manage/promounittest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_promo_unit_3',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $expected_path = 'utexas_image_style_112w_112h/public/image-test.png';
    $assert->elementAttributeContains('css', 'a[href^="https://promounit.test"] picture img', 'src', $expected_path);

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/promounittest/delete');
    $this->submitForm([], 'Remove');
  }

}
