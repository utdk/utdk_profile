<?php

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Test input/output of Flex List field type via the Flex List block type.
 */
trait FlexListTestTrait {

  /**
   * Test schema.
   */
  public function verifyFlexList() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Flex List';
    $block_type_id = 'utexas_flex_list';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Flex List Item 1 fields.
    $form->fillField('field_utexas_flex_list_items[0][header]', 'Location');
    $form->fillField('field_utexas_flex_list_items[0][content][format]', 'restricted_html');
    $form->fillField('field_utexas_flex_list_items[0][content][value]', 'FAC 326');
    // Add Flex List Item 2 and fill fields.
    $this->addDraggableFormItem($form, 'Add another item');
    $form->fillField('field_utexas_flex_list_items[1][content][format]', 'restricted_html');
    $form->fillField('field_utexas_flex_list_items[1][header]', 'Website');
    $form->fillField('field_utexas_flex_list_items[1][content][value]', 'https://drupalkit.its.utexas.edu');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->linkByHrefExists('https://drupalkit.its.utexas.edu');
    $assert->elementExists('css', '.utexas-flex-list.formatter-default h5#location');
    $assert->elementExists('css', '.utexas-flex-list.formatter-default h5#website');

    // CRUD: UPDATE
    // Set display to "Accordions".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_flex_list__accordion'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', '.utexas-flex-list.formatter-accordion details summary');

    // CRUD: UPDATE
    // Set display to "Horizontal Tabs".
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form_values = ['settings[view_mode]' => 'utexas_flex_list__horizontal_tabs'];
    $this->updateBlockOnFlexPage($block_name, $form_values);
    $this->savePageLayout();

    // CRUD: READ
    // Verify page output.
    $assert->elementExists('css', '.utexas-flex-list.formatter-htabs ul.nav-tabs li.nav-item.active');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

}
