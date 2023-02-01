<?php

namespace Drupal\Tests\utexas\Traits\WidgetsTestTraits;

/**
 * Verifies QuickLinks field schema & validation.
 */
trait QuickLinksTestTrait {

  /**
   * Test Quick Links.
   */
  public function verifyQuickLinks() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    // Block info.
    $block_type = 'Quick Links';
    $block_type_id = 'utexas_quick_links';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';
    $block_name = $block_type . ' Test';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Quick Links fields.
    $form->fillField('field_block_ql[0][headline]', 'Quick Links Headline');
    // Fill Quick Links Item 1 fields.
    $form->fillField('field_block_ql[0][links][0][title]', 'Quick Links Link!');
    $form->fillField('field_block_ql[0][links][0][uri]', 'https://tylerfahey.com');
    $form->fillField('field_block_ql[0][links][0][options][attributes][target][_blank]', ['_blank' => '_blank']);
    $form->fillField('field_block_ql[0][links][0][options][attributes][class]', 'ut-cta-link--external');
    // Add Quick Links Item 2 and fill fields.
    $this->addNonDraggableFormItem($form, 'Add link');
    $form->fillField('field_block_ql[0][links][1][title]', 'Quick Links Link Number 2!');
    $form->fillField('field_block_ql[0][links][1][uri]', '/node/' . $flex_page_id);
    $form->fillField('field_block_ql[0][links][1][options][attributes][class]', 'ut-cta-link--lock');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Verify Quick Links headline, is present.
    $assert->responseContains('Quick Links Headline');
    // Verify Quick Links link, delta 0, is present, is an external link, and
    // has appropriate options.
    $assert->responseContains('<a href="https://tylerfahey.com" rel="noopener noreferrer" class="ut-cta-link--external ut-link" target="_blank">Quick Links Link!</a>');
    // Verify Quick Links link, delta 1, is present, is an internal link, and
    // has appropriate options.
    $assert->responseContains('<a href="/test-flex-page" class="ut-cta-link--lock ut-link">Quick Links Link Number 2!</a>');
    // An automatic anchor (ID) has been added to the headline.
    $assert->elementExists('css', '#quick-links-headline');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block-content');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Add Quick Links Item 3 and fill fields.
    $this->addNonDraggableFormItem($form, 'Add link');
    $form->fillField('field_block_ql[0][links][2][uri]', 'https://quicklinks.test');
    $form->fillField('field_block_ql[0][links][2][title]', 'Third link');
    // Empty Quick Links Item 2.
    $form->fillField('field_block_ql[0][links][1][uri]', '');
    $form->fillField('field_block_ql[0][links][1][title]', '');
    $form->fillField('field_block_ql[0][links][1][options][attributes][class]', '0');
    $form->uncheckField('field_block_ql[0][links][1][options][attributes][target][_blank]');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been updated.');

    // CRUD: READ.
    $this->drupalGet('admin/content/block-content');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $this->waitForForm($block_content_edit_form_id);
    // Confirm second link has data from third link previously created.
    $assert->fieldValueEquals('field_block_ql[0][links][1][title]', 'Third link');
    $assert->fieldValueEquals('field_block_ql[0][links][1][uri]', 'https://quicklinks.test');
    // Assert former second link is now gone.
    $assert->pageTextNotContains('Quick Links Link Number 2!');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
    $this->removeNodes([$flex_page_id]);
  }

}
