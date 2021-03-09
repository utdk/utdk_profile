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
    $session = $this->getSession();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Hero';
    $block_name = $block_type . 'Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Open the media library.
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

    $assert->elementExists('css', '#edit-field-block-hero-0-disable-image-styles');
    // Disable image style.
    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_hero[0][heading]' => 'Hero Heading',
      'field_block_hero[0][subheading]' => 'Hero Subheading',
      'field_block_hero[0][caption]' => 'Hero Caption',
      'field_block_hero[0][credit]' => 'Hero Credit',
      'field_block_hero[0][cta][link][uri]' => 'https://hero.test',
      'field_block_hero[0][cta][link][title]' => 'Hero CTA',
      'field_block_hero[0][disable_image_styles]' => '1',
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

    // Verify image styles are disabled.
    $expected_path = '/files/image-test.png';
    $assert->elementAttributeContains('css', '.ut-hero img', 'src', $expected_path);

    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();

    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_hero[0][heading]' => 'Hero Heading',
      'field_block_hero[0][subheading]' => 'Hero Subheading',
      'field_block_hero[0][caption]' => 'Hero Caption',
      'field_block_hero[0][credit]' => 'Hero Credit',
      'field_block_hero[0][cta][link][uri]' => 'https://hero.test',
      'field_block_hero[0][cta][link][title]' => 'Hero CTA',
      'field_block_hero[0][cta][link][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_hero[0][cta][link][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_hero[0][disable_image_styles]' => '0',
    ], 'Save');
    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been updated.');

    $this->drupalGet('node/' . $flex_page);
    // Verify page output.
    $assert->pageTextNotContains('Hero Heading');
    $assert->pageTextNotContains('Hero Subheading');
    $assert->pageTextContains('Hero Caption');
    $assert->pageTextContains('Hero Credit');
    // The default display does not include a CTA.
    $assert->linkByHrefNotExists('https://hero.test');
    $assert->pageTextNotContains('Hero CTA');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'picture source');
    $expected_path = 'utexas_image_style_720w_389h/public/image-test.png';
    $assert->elementAttributeContains('css', 'picture img', 'src', $expected_path);

    // Set to Style 1.
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $hero_style_1 = [
      'edit-hero-style' => 'utexas_hero_1',
      'edit-anchor-position' => 'center',
    ];
    $this->submitForm($hero_style_1, 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    // Verify page output.
    $assert->elementExists('css', '.hero--photo-orange-insert .hero-img');
    // Verify anchor class is set.
    $assert->elementExists('css', '.hero--photo-orange-insert .center');
    // Verify that the correct image style is being applied.
    // Since the screen width is 1200, we expect utexas_image_style_2250w_900h.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-orange-insert .hero-img").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);
    // The Hero style 1 does include a CTA.
    $assert->linkByHrefExists('https://hero.test');
    $assert->pageTextContains('Hero CTA');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');

    // Set display to "Hero Style 1 Left".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $hero_style_1_left = [
      'edit-hero-style' => 'utexas_hero_1',
      'edit-anchor-position' => 'left',
    ];
    $this->submitForm($hero_style_1_left, 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    // Verify page output.
    $assert->elementExists('css', '.hero--photo-orange-insert .hero-img');
    // Verify anchor class is set.
    $assert->elementExists('css', '.hero--photo-orange-insert .left');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-orange-insert .hero-img").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // Set display to "Hero Style 2".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));
    $hero_style_2 = [
      'edit-hero-style' => 'utexas_hero_2',
      'edit-anchor-position' => 'center',
    ];
    $this->submitForm($hero_style_2, 'Update');

    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output with anchor.
    $assert->elementExists('css', '.hero--photo-gradient');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-gradient").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // Set display to "Hero Style 2 Right".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));

    $hero_style_2_right = [
      'edit-hero-style' => 'utexas_hero_2',
      'edit-anchor-position' => 'right',
    ];
    $this->submitForm($hero_style_2_right, 'Update');

    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output with anchor.
    $assert->elementExists('css', '.hero--photo-gradient.right');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".hero--photo-gradient").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // Set display to "Hero Style 3".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));

    $hero_style_3 = [
      'edit-hero-style' => 'utexas_hero_3',
    ];
    $this->submitForm($hero_style_3, 'Update');

    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $assert->elementExists('css', '.ut-hero.hero--photo-white-notch');
    // Verify that the correct image style is being applied.
    // Since the screen width is 900, we expect an image style of 900w.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".ut-hero.hero--photo-white-notch").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

    // Set display to "Hero Style 4".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));

    $hero_style_4 = [
      'edit-hero-style' => 'utexas_hero_4',
    ];
    $this->submitForm($hero_style_4, 'Update');

    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $assert->elementExists('css', '.ut-hero.hero--blue-bar');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'picture source');
    $expected_path = 'utexas_image_style_720w_389h/public/image-test.png';
    $assert->elementAttributeContains('css', 'picture img', 'src', $expected_path);

    // Set display to "Hero Style 5".
    $this->drupalGet('node/' . $flex_page . '/layout');
    $this->clickContextualLink('.block-block-content' . $this->drupalGetBlockByInfo($block_name)->uuid(), 'Configure');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'layout-builder-modal',
    ]));

    $hero_style_5 = [
      'edit-hero-style' => 'utexas_hero_5',
    ];
    $this->submitForm($hero_style_5, 'Update');

    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    // Verify page output.
    $assert->elementExists('css', '.ut-hero.hero--half-n-half');
    // Verify that the correct image style is being applied.
    $background_image_url = $this->getSession()->evaluateScript('jQuery(".ut-hero.hero--half-n-half .hero-img").css("background-image")');
    $pos = strpos($background_image_url, 'utexas_image_style_2250w_900h/public/image-test.png');
    $this->assertTrue($pos !== FALSE);

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
