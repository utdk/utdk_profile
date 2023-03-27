<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

/**
 * Verifies custom link options behavior.
 *
 * @group utexas
 */
class LinkOptionsTest extends FunctionalJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->testSiteManagerUser);
  }

  /**
   * Initial action for all layout tests.
   */
  public function testLinkOptions() {
    // CRUD: CREATE
    // Create flex page.
    $flex_page_id = $this->createFlexPage();

    $this->verifyIcons($flex_page_id);
    $this->verifyUrls($flex_page_id);

    // CRUD: DELETE.
    $this->removeNodes([$flex_page_id]);
  }

  /**
   * Test link icons provided by the UTexasLinkOptions widget.
   *
   * @param string $flex_page_id
   *   The node ID of the Layout Builder enabled page in question.
   */
  public function verifyIcons($flex_page_id) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // Block info.
    $block_type = 'Quick Links';
    $block_type_id = 'utexas_quick_links';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_name = 'Link Options Test';
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';
    $block_content_edit_form_id = 'block-content-' . $block_plugin_id . '-edit-form';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Quick Links fields.
    $form->fillField('field_block_ql[0][headline]', 'Quick Links Headline');
    // Fill Quick Links links[0] fields.
    $form->fillField('field_block_ql[0][links][0][title]', 'Link Number 1');
    $form->fillField('field_block_ql[0][links][0][uri]', 'https://www.utexas.edu');
    $form->checkField('field_block_ql[0][links][0][options][attributes][target][_blank]');
    $form->fillField('field_block_ql[0][links][0][options][attributes][class]', 'ut-cta-link--external');
    // Add Quick Links links[1] and fill fields.
    $this->addNonDraggableFormItem($form, 'Add link');
    $form->fillField('field_block_ql[0][links][1][title]', 'Link Number 2');
    $form->fillField('field_block_ql[0][links][1][uri]', '/node/' . $flex_page_id);
    $form->fillField('field_block_ql[0][links][1][options][attributes][class]', 'ut-cta-link--lock');
    // Add Quick Links links[2] and fill fields.
    $this->addNonDraggableFormItem($form, 'Add link');
    $form->fillField('field_block_ql[0][links][2][title]', 'Link Number 3');
    $form->fillField('field_block_ql[0][links][2][uri]', '<front>');
    $form->fillField('field_block_ql[0][links][2][options][attributes][class]', 'ut-cta-link--angle-right');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Delta 0 is present, links to an external site, shows an external icon,
    // and opens in a new tab.
    $assert->responseContains('<a href="https://www.utexas.edu" rel="noopener noreferrer" class="ut-cta-link--external ut-link" target="_blank">Link Number 1</a>');
    // Delta 1 is present, links to an internal page, and has a lock.
    $assert->responseContains('<a href="/test-flex-page" class="ut-cta-link--lock ut-link">Link Number 2</a>');
    // Delta 2 is present, links to the front page, and has a caret.
    $assert->responseContains('<a href="/" class="ut-cta-link--angle-right ut-link">Link Number 3</a>');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block-content');
    $this->scrollLinkIntoViewAndClick($page, $block_name);
    $form = $this->waitForForm($block_content_edit_form_id);
    // Fill Quick Links links[2] fields.
    $form->fillField('field_block_ql[0][links][2][uri]', 'https://quicklinks.test');
    $form->fillField('field_block_ql[0][links][2][title]', 'Updated third link');
    $form->fillField('field_block_ql[0][links][2][options][attributes][class]', '0');
    // Empty Quick Links links[1] fields.
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
    $assert->fieldValueEquals('field_block_ql[0][links][1][title]', 'Updated third link');
    $assert->fieldValueEquals('field_block_ql[0][links][1][uri]', 'https://quicklinks.test');
    $assert->fieldValueEquals('field_block_ql[0][links][1][options][attributes][class]', '0');
    // Assert former second link is now gone.
    $assert->pageTextNotContains('Link Number 2');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
  }

  /**
   * Test URL variations to make sure they're processed correctly.
   *
   * @param string $flex_page_id
   *   The node ID of the Layout Builder enabled page in question.
   */
  public function verifyUrls($flex_page_id) {
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();

    // Block info.
    $block_type = 'Quick Links';
    $block_type_id = 'utexas_quick_links';
    $block_plugin_id = str_replace('_', '-', $block_type_id);
    $block_name = 'Link URLs Test';
    $block_content_create_form_id = 'block-content-' . $block_plugin_id . '-form';

    // CRUD: CREATE.
    $this->drupalGet('block/add/' . $block_type_id);
    $form = $this->waitForForm($block_content_create_form_id);
    // Fill Block description field.
    $form->fillField('info[0][value]', $block_name);
    // Fill Link 1 fields.
    $form->fillField('field_block_ql[0][links][0][title]', 'Internal link with anchor');
    $form->fillField('field_block_ql[0][links][0][uri]', '/node/' . $flex_page_id . '#anchor');
    // Add Link 2 and fill fields.
    $this->addNonDraggableFormItem($form, 'Add link');
    $form->fillField('field_block_ql[0][links][1][title]', 'Internal link with query');
    $form->fillField('field_block_ql[0][links][1][uri]', '/node/' . $flex_page_id . '?query=1&search=test');
    // Add Link 3 and fill fields.
    $this->addNonDraggableFormItem($form, 'Add link');
    $form->fillField('field_block_ql[0][links][2][title]', 'Link to front with query and anchor');
    $form->fillField('field_block_ql[0][links][2][uri]', '/#anchor?query=1&search=test');
    // Save block.
    $form->pressButton('Save');
    $assert->statusMessageContainsAfterWait($block_type . ' ' . $block_name . ' has been created.');
    // Place the block on the Flex page.
    $this->drupalGetNodeLayoutTab($flex_page_id);
    $form = $this->waitForForm('node-utexas-flex-page-layout-builder-form');
    $this->placeExistingBlockOnFlexPage($form, $block_name);
    $this->savePageLayout();

    // CRUD: READ
    // Delta 0 is present, links to an external site, shows an external icon,
    // and opens in a new tab.
    $assert->responseContains('<a href="/test-flex-page#anchor" class="ut-link">Internal link with anchor</a>');
    // Delta 1 is present, links to an internal page, and has a lock.
    $assert->responseContains('<a href="/test-flex-page?query=1&amp;search=test" class="ut-link">Internal link with query</a>');
    // Delta 2 is present, links to the front page, and has a caret.
    $assert->responseContains('<a href="/#anchor?query=1&amp;search=test" class="ut-link">Link to front with query and anchor</a>');

    // CRUD: DELETE.
    $this->removeBlocks([$block_name]);
  }

}
