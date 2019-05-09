<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Resource schema & validation.
 */
trait ResourcesTestTrait {

  /**
   * Verify promo unit widget schema & output.
   */
  public function verifyResources() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->drupalGet('block/add/utexas_resources');

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

    // Verify that multiple links can be added.
    $page->pressButton('Add link');
    $assert->assertWaitOnAjaxRequest();

    // Verify that multiple resource collections can be added.
    $page->pressButton('Add another collection');
    $assert->assertWaitOnAjaxRequest();

    $this->submitForm([
      'info[0][value]' => 'Resources Test',
      'field_block_resources[0][headline]' => 'Resource Container Headline',
      'field_block_resources[0][resource_items][0][item][headline]' => 'Resource 1 Headline',
      'field_block_resources[0][resource_items][0][item][links][0][url]' => 'https://resource.test',
      'field_block_resources[0][resource_items][0][item][links][0][title]' => 'Resource External Link',
      'field_block_resources[0][resource_items][0][item][links][1][url]' => '/node',
      'field_block_resources[0][resource_items][0][item][links][1][title]' => 'Resource Internal Link',
      'field_block_resources[0][resource_items][1][item][headline]' => 'Resource 2 Headline',
    ], 'Save');
    $assert->pageTextContains('Resources Resources Test has been created.');

    // Place Block in "Content" region on all pages.
    $this->submitForm([
      'region' => 'content',
    ], 'Save block');
    $assert->pageTextContains('The block configuration has been saved.');

    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementTextContains('css', 'h3.ut-headline--underline', 'Resource Container Headline');
    $assert->elementTextContains('css', 'h3.ut-headline', 'Resource 1 Headline');
    $assert->pageTextContains('Resource 2 Headline');
    $assert->pageTextContains('Resource Internal Link');
    $assert->linkByHrefExists('https://resource.test');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'picture source');
    $expected_path = 'utexas_image_style_400w_250h/public/image-test';
    $assert->elementAttributeContains('css', 'picture img', 'src', $expected_path);

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/resourcestest/delete');
    $this->submitForm([], 'Remove');
  }

}
