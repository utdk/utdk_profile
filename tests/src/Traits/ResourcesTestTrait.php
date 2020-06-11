<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Resource schema & validation.
 */
trait ResourcesTestTrait {

  /**
   * Verify Resources widget schema & output.
   */
  public function verifyResources() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
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

    // CRUD: READ.
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

    // CRUD: UPDATE.
    // Edit block to add more links.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Resources Test')->click();
    // Reorder instances.
    $this->submitForm([
      'field_block_resources[0][resource_items][items][0][weight]' => 1,
      'field_block_resources[0][resource_items][items][1][weight]' => 0,
    ], 'Save');
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Resources Test')->click();
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-resources details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }

    $page->pressButton('Add link');
    $assert->assertWaitOnAjaxRequest();

    // Populate the third link.
    $page->fillField('field_block_resources[0][resource_items][items][0][details][item][links][2][uri]', 'https://thirdlink.test');
    $page->fillField('field_block_resources[0][resource_items][items][0][details][item][links][2][title]', 'Third link');

    // Remove data for the second link
    // (#1121: Verify links can be removed without loss of data.)
    $page->fillField('field_block_resources[0][resource_items][items][0][details][item][links][1][uri]', '');
    $page->fillField('field_block_resources[0][resource_items][items][0][details][item][links][1][title]', '');
    $page->fillField('field_block_resources[0][resource_items][items][0][details][item][links][1][options][attributes][class]', '0');

    // Save block data and assert links are reordered.
    $page->pressButton('edit-submit');

    // View the block form.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Resources Test')->click();
    $fieldset = $page->findAll('css', '#edit-field-block-resources-0-resource-items-items-0-details');
    $fieldset[0]->click();
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-resources details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Confirm second link has data from third link previously created.
    $this->assertSession()->fieldValueEquals('field_block_resources[0][resource_items][items][0][details][item][links][1][title]', 'Third link');
    $this->assertSession()->fieldValueEquals('field_block_resources[0][resource_items][items][0][details][item][links][1][uri]', 'https://thirdlink.test');
    // Verify data for removed link is not present.
    $assert->pageTextNotContains('Resources Internal Link');

    // Verify Resource collections can be removed.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Resources Test')->click();
    // Add a third item.
    $page->pressButton('Add another collection');
    $assert->assertWaitOnAjaxRequest();
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-resources details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Clear out the data for item 2; add item 3.
    $this->createScreenshot('before.png');
    $page->fillField('field_block_resources[0][resource_items][items][1][details][item][headline]', '');
    $page->fillField('field_block_resources[0][resource_items][items][1][details][item][links][0][uri]', '');
    $page->fillField('field_block_resources[0][resource_items][items][2][details][item][headline]', 'Resource 3 Headline');
    $page->pressButton('edit-submit');

    // Go back to the edit form.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Resources Test')->click();
    // Expand collapsed instances.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-resources details');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Verify data for item entered in slot 3 is deposited in the empty slot 2.
    $assert->fieldValueEquals('field_block_resources[0][resource_items][items][1][details][item][headline]', 'Resource 3 Headline');
    // Verify data for removed item is not present.
    $assert->pageTextNotContains('Resource 2 Headline');

    // CRUD: DELETE.
    // Reset block weighting system for subsequent tests.
    $this->drupalGet('/admin/structure/block/block-content');
    $checkbox_selector = '.views-field-operations li.edit';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $page->pressButton('Hide row weights');

    // Remove the block from the system.
    $this->drupalGet('admin/structure/block/block-content');
    $page->findLink('Resources Test')->click();
    $page->clickLink('Delete');
    $page->pressButton('Delete');
    $this->drupalGet('admin/structure/block/block-content');
    $assert->pageTextNotContains('Resources Test');

    // TEST CLEANUP //
    // Remove test node.
    $storage_handler = \Drupal::entityTypeManager()->getStorage("node");
    $entities = $storage_handler->loadMultiple([$basic_page_id]);
    $storage_handler->delete($entities);
  }

  /**
   * Verify Resources links within collections are always displayed.
   */
  public function verifyResourceCollectionLinks() {
    $assert = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();

    $this->drupalGet("/node/add/utexas_flex_page");
    $edit = [
      'title[0][value]' => 'Resource Collection Links Test',
    ];
    // Create Flex Page node.
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $node = $this->drupalGetNodeByTitle('Resource Collection Links Test');
    // Go to layout tab for node.
    $this->drupalGet('node/' . $node->id() . '/layout');
    // Add a new resources block.
    $this->clickLink('Add block');
    $assert->waitForText('Create custom block');
    $this->clickLink('Create custom block');
    $assert->waitForText('Add a new Inline Block');
    $this->clickLink('Resources');
    // Verify that the add block has been opened in the modal.
    $assert->waitForText('Resource Title');
    // Fill block title.
    $page->fillField('settings[label]', 'Resources Block');
    // Add second collection.
    $page->pressButton('Add another collection');
    $assert->waitForText('Resource Collection 2');
    // Retrieve all collection fieldsets into a variable.
    $fieldsets = $page->findAll('css', 'div.field--type-utexas-resources details summary');
    // Open the first collection.
    $fieldsets[0]->click();
    // Add second link to first collection, 2 total.
    $page->pressButton('Add link');
    $one = 'settings[block_form][field_block_resources][0][resource_items][items][0][details][item]';
    $assert->waitForElement('css', 'input[name="' . $one . '[links][1][uri]"]');

    // Fill all inputs for first collection.
    $page->fillField($one . '[headline]', 'Resource Container 1 Headline');
    $page->fillField($one . '[links][0][title]', 'Link 1');
    $page->fillField($one . '[links][0][uri]', 'https://resource.test');
    $page->fillField($one . '[links][1][title]', 'Link 2');
    $page->fillField($one . '[links][1][uri]', 'https://resource2.test');
    // Close first collection.
    $fieldsets[0]->click();
    $session->wait(3000);
    // Open second collection after closing first one.
    $fieldsets[1]->click();
    $two = 'settings[block_form][field_block_resources][0][resource_items][items][1][details][item]';
    // Add two more links to second collection, 3 total.
    $page->pressButton('field_block_resources01');
    $assert->waitForElement('css', 'input[name="' . $two . '[links][1][uri]"]');
    $page->pressButton('field_block_resources01');
    $assert->waitForElement('css', 'input[name="' . $two . '[links][2][uri]"]');
    // Fill all inputs for second collection.
    $page->fillField($two . '[headline]', 'Resource Container 1 Headline');
    $page->fillField($two . '[links][0][title]', 'Link 3');
    $page->fillField($two . '[links][0][uri]', 'https://resource.test');
    $page->fillField($two . '[links][1][title]', 'Link 4');
    $page->fillField($two . '[links][1][uri]', 'https://resource2.test');
    $page->fillField($two . '[links][2][title]', 'Link 5');
    $page->fillField($two . '[links][2][uri]', '/');
    $page->pressButton('Add block');
    $assert->waitForText('You have unsaved changes.');
    $this->clickContextualLink('.block-inline-blockutexas-resources', 'Configure');
    $assert->waitForText('Resource Title');
    foreach ($fieldsets as $fieldset) {
      $fieldset->click();
    }
    // Verify there are 5 links between both collections, no missing links.
    $assert->fieldValueEquals($one . '[links][0][title]', 'Link 1');
    $assert->fieldValueEquals($one . '[links][1][title]', 'Link 2');
    $assert->fieldValueEquals($two . '[links][0][title]', 'Link 3');
    $assert->fieldValueEquals($two . '[links][1][title]', 'Link 4');
    $assert->fieldValueEquals($two . '[links][2][title]', 'Link 5');
  }

}
