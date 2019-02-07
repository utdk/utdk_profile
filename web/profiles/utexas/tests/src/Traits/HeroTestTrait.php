<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Hero field schema & validation.
 */
trait HeroTestTrait {

  /**
   * Test schema.
   */
  public function verifyHero() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('block/add/utexas_hero');

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
      'info[0][value]' => 'Hero Test',
      'field_block_hero[0][heading]' => 'Hero Heading',
      'field_block_hero[0][subheading]' => 'Hero Subheading',
      'field_block_hero[0][caption]' => 'Hero Caption',
      'field_block_hero[0][credit]' => 'Hero Credit',
      'field_block_hero[0][cta][link][url]' => 'https://hero.test',
      'field_block_hero[0][cta][link][title]' => 'Hero CTA',
    ], 'Save');
    $assert->pageTextContains('Hero Hero Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');
    // Verify page output.
    $assert->pageTextNotContains('Hero Heading');
    $assert->pageTextNotContains('Hero Subheading');
    $assert->pageTextContains('Hero Caption');
    $assert->pageTextContains('Hero Credit');
    $assert->linkByHrefNotExists('https://hero.test');
    $assert->pageTextNotContains('Hero CTA');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'picture source');
    $expected_path = 'utexas_image_style_720w_389h/public/image-test.png';
    $assert->elementAttributeContains('css', 'picture img', 'src', $expected_path);

    // Set display to "Hero Style 1".
    $this->drupalGet('admin/structure/block/manage/herotest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_hero_1',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', '.hero--photo-orange-insert .hero-img');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-orange-insert .hero-img").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_900w/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // Set display to "Hero Style 2".
    $this->drupalGet('admin/structure/block/manage/herotest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_hero_2',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', '.hero--photo-gradient');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-gradient").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_900w/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // Set display to "Hero Style 3".
    $this->drupalGet('admin/structure/block/manage/herotest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_hero_3',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', '.ut-hero.hero--photo-white-notch');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".ut-hero.hero--photo-white-notch").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_900w/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // Set display to "Hero Style 4".
    $this->drupalGet('admin/structure/block/manage/herotest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_hero_4',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', '.ut-hero.hero--blue-bar');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'picture source');
    $expected_path = 'utexas_image_style_720w_389h/public/image-test.png';
    $assert->elementAttributeContains('css', 'picture img', 'src', $expected_path);

    // Set display to "Hero Style 5".
    $this->drupalGet('admin/structure/block/manage/herotest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_hero_5',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', '.ut-hero.hero--half-n-half');
    // Verify that the correct image style is being applied.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".ut-hero.hero--half-n-half").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_900w/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/herotest/delete');
    $this->submitForm([], 'Remove');
  }

}
