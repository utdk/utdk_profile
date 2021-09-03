<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Verifies custom link options behavior.
 *
 * @group utexas
 */
class UTexasLinkOptionsTest extends WebDriverTestBase {
  use EntityTestTrait;
  use TestFileCreationTrait;
  use InstallTestTrait;
  use UserTestTrait;

  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

  /**
   * Specify the theme to be used in testing.
   *
   * @var string
   */
  protected $defaultTheme = 'forty_acres';

  /**
   * An user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeContentEditor();
    $this->drupalLogin($this->testUser);
  }

  /**
   * Test link icons provided by the UTexasLinkOptions widget.
   */
  public function testIcons() {
    $this->getSession()->resizeWindow(900, 2000);
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Quick Links';
    $block_name = 'Link Options Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Add two additional link inputs.
    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_ql[0][links][1][uri]',
    ]));
    $assert->elementExists('css', '.js-form-item-field-block-ql-0-links-1-uri');

    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_ql[0][links][2][uri]',
    ]));
    $assert->elementExists('css', '.js-form-item-field-block-ql-0-links-2-uri');

    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_ql[0][headline]' => 'Quick Links Headline',
      'field_block_ql[0][links][0][title]' => 'Link Number 1',
      'field_block_ql[0][links][0][uri]' => 'https://www.utexas.edu',
      'field_block_ql[0][links][0][options][attributes][target][_blank]' => ['_blank' => '_blank'],
      'field_block_ql[0][links][0][options][attributes][class]' => 'ut-cta-link--external',
      'field_block_ql[0][links][1][title]' => 'Link Number 2',
      'field_block_ql[0][links][1][uri]' => '/node/' . $flex_page,
      'field_block_ql[0][links][1][options][attributes][class]' => 'ut-cta-link--lock',
      'field_block_ql[0][links][2][title]' => 'Link Number 3',
      'field_block_ql[0][links][2][uri]' => '<front>',
      'field_block_ql[0][links][2][options][attributes][class]' => 'ut-cta-link--angle-right',
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

    // Delta 0 is present, links to an external site, shows an external icon,
    // and opens in a new tab.
    $this->assertRaw('<a href="https://www.utexas.edu" rel="noopener noreferrer" class="ut-cta-link--external ut-link" target="_blank">Link Number 1</a>');
    // Delta 1 is present, links to an internal page, and has a lock.
    $this->assertRaw('<a href="/test-flex-page" class="ut-cta-link--lock ut-link">Link Number 2</a>');
    // Delta 2 is present, links to the front page, and has a caret.
    $this->assertRaw('<a href="/" class="ut-cta-link--angle-right ut-link">Link Number 3</a>');

    // CRUD: UPDATE.
    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    // Change the third link
    $page->fillField('field_block_ql[0][links][2][uri]', 'https://quicklinks.test');
    $page->fillField('field_block_ql[0][links][2][title]', 'Updated third link');
    $page->fillField('field_block_ql[0][links][2][options][attributes][class]', '0');
    // Empty second link.
    $page->fillField('field_block_ql[0][links][1][uri]', '');
    $page->fillField('field_block_ql[0][links][1][title]', '');
    $page->fillField('field_block_ql[0][links][1][options][attributes][class]', '0');
    $page->uncheckField('field_block_ql[0][links][1][options][attributes][target][_blank]');
    // Save block data and assert links are reordered.
    $page->pressButton('edit-submit');

    $this->drupalGet('admin/content/block-content');
    $page->findLink($block_name)->click();
    // Confirm second link has data from third link previously created.
    $assert->fieldValueEquals('field_block_ql[0][links][1][title]', 'Updated third link');
    $assert->fieldValueEquals('field_block_ql[0][links][1][uri]', 'https://quicklinks.test');
    $assert->fieldValueEquals('field_block_ql[0][links][1][options][attributes][class]', '0');
    // Assert former second link is now gone.
    $assert->pageTextNotContains('Link Number 2');

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

  /**
   * Test URL variations to make sure they're processed correctly.
   */
  public function testUrls() {
    $this->getSession()->resizeWindow(900, 2000);
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Create a Flex Page.
    $flex_page = $this->createFlexPage();

    // CRUD: CREATE.
    $block_type = 'Quick Links';
    $block_name = 'Link URLs Test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Add two additional link inputs.
    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_ql[0][links][1][uri]',
    ]));
    $assert->elementExists('css', '.js-form-item-field-block-ql-0-links-1-uri');

    $page->pressButton('Add link');
    $this->assertNotEmpty($assert->waitForElementVisible('named', [
      'id_or_name',
      'field_block_ql[0][links][2][uri]',
    ]));
    $assert->elementExists('css', '.js-form-item-field-block-ql-0-links-2-uri');

    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_ql[0][links][0][title]' => 'Internal link with anchor',
      'field_block_ql[0][links][0][uri]' => '/node/' . $flex_page . '#anchor',
      'field_block_ql[0][links][1][title]' => 'Internal link with query',
      'field_block_ql[0][links][1][uri]' => '/node/' . $flex_page . '?query=1&search=test',
      'field_block_ql[0][links][2][title]' => 'Link to front with query and anchor',
      'field_block_ql[0][links][2][uri]' => '/#anchor?query=1&search=test',
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

    // Delta 0 is present, links to an external site, shows an external icon,
    // and opens in a new tab.
    $this->assertRaw('<a href="/test-flex-page#anchor" class="ut-link">Internal link with anchor</a>');
    // Delta 1 is present, links to an internal page, and has a lock.
    $this->assertRaw('<a href="/test-flex-page?query=1&amp;search=test" class="ut-link">Internal link with query</a>');
    // Delta 2 is present, links to the front page, and has a caret.
    $this->assertRaw('<a href="/#anchor?query=1&amp;search=test" class="ut-link">Link to front with query and anchor</a>');

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
