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
    $fieldset = $page->findAll('css', '#edit-field-block-resources-0-resource-items-items-0-details');
    $fieldset[0]->click();

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

    // Verify that multiple links can be added.
    $page->pressButton('Add link');
    $assert->assertWaitOnAjaxRequest();

    // Verify that multiple resource collections can be added.
    $page->pressButton('Add another collection');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Show row weights');
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-resources details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    // Create test node.
    $basic_page_id = $this->createBasicPage();

    $this->submitForm([
      'info[0][value]' => 'Resources Test',
      'field_block_resources[0][headline]' => 'Resource Container Headline',
      'field_block_resources[0][resource_items][items][0][details][item][headline]' => 'Resource 1 Headline',
      'field_block_resources[0][resource_items][items][0][details][item][links][0][uri]' => 'https://resource.test',
      'field_block_resources[0][resource_items][items][0][details][item][links][0][title]' => 'Resource External Link',
      'field_block_resources[0][resource_items][items][0][details][item][links][0][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_resources[0][resource_items][items][0][details][item][links][0][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_resources[0][resource_items][items][0][details][item][links][1][uri]' => '/node/' . $basic_page_id,
      'field_block_resources[0][resource_items][items][0][details][item][links][1][title]' => 'Resource Internal Link',
      'field_block_resources[0][resource_items][items][0][details][item][links][1][options][attributes][class]' => 'ut-cta-link--lock',
      'field_block_resources[0][resource_items][items][1][details][item][headline]' => 'Resource 2 Headline',
      'field_block_resources[0][resource_items][items][0][weight]' => 1,
      'field_block_resources[0][resource_items][items][1][weight]' => 0,
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
    // User-supplied weighting of resource items is respected.
    $assert->elementTextContains('xpath', '//*[@id="block-resourcestest"]/div[2]/div/div[1]/div/h3', 'Resource 2 Headline');
    $assert->elementTextContains('xpath', '//*[@id="block-resourcestest"]/div[2]/div/div[2]/div[2]/h3', 'Resource 1 Headline');
    $assert->pageTextContains('Resource Internal Link');
    $assert->linkByHrefExists('https://resource.test');
    // Verify links exist with options.
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'target', '_blank');
    $assert->elementAttributeContains('css', '.ut-cta-link--external', 'rel', 'noopener noreferrer');
    $assert->elementExists('css', '.ut-cta-link--lock');
    // Verify responsive image is present within the link.
    $assert->elementExists('css', 'picture source');
    $expected_path = 'utexas_image_style_400w_250h/public/image-test';
    $assert->elementAttributeContains('css', 'picture img', 'src', $expected_path);
    // Verify stacked display adding class to markup.
    $this->drupalGet('admin/structure/block/manage/resourcestest');
    $this->submitForm([
      'region' => 'content',
      'settings[view_mode]' => 'utexas_resources_2',
    ], 'Save block');
    $this->drupalGet('<front>');
    // Verify page output.
    $assert->elementExists('css', 'div.stacked-display div.ut-resources-wrapper');

    // Reset block weighting system.
    $this->drupalGet('/admin/structure/block/block-content');
    $checkbox_selector = '.views-field-operations li.edit';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $page->pressButton('Hide row weights');

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/manage/resourcestest/delete');
    $this->submitForm([], 'Remove');

    // Remove test node.
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple([$basic_page_id]);
    $storage_handler->delete($entities);
  }

}
