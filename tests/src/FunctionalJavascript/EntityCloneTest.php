<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;


use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\Traits\Core\CronRunTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\contextual\FunctionalJavascript\ContextualLinkClickTrait;
use Drupal\Tests\utexas\Traits\LayoutBuilderIntegrationTestTrait;

/**
 * Demonstrate that various node types can be cloned.
 *
 * @group utexas
 */
class EntityCloneTest extends WebDriverTestBase {
  use CronRunTrait;
  use EntityTestTrait;
  use InstallTestTrait;
  use TestFileCreationTrait;
  use UserTestTrait;
  use LayoutBuilderIntegrationTestTrait;
  use ContextualLinkClickTrait;

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
   * An image uri to be used with file uploads.
   *
   * @var string
   */
  protected $testImage;

  /**
   * An video Media ID to be used with file rendering.
   *
   * @var string
   */
  protected $testVideo;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeSiteManager();
    $this->drupalLogin($this->testUser);
    $this->testImage = $this->createTestMediaImage();
    $this->testVideo = $this->createTestMediaVideoExternal();
  }

  /**
   * Clone a Flex Page.
   */
  public function testFlexPage() {
    $this->getSession()->resizeWindow(1500, 4000);
    /** @var Drupal\Tests\WebAssert $assert */
    $assert = $this->assertSession();
    $session = $this->getSession();
    $page = $this->getSession()->getPage();
    $original_id = $this->createFlexPage();
    $clone_id = $original_id + 1;

    $block_type = 'Featured Highlight';
    $block_name = 'Reusable block test';
    $this->drupalGet('admin/content/block-content');
    $this->clickLink('Add custom block');
    $this->clickLink($block_type);

    // Open the media library.
    $session->wait(3000);
    $page->pressButton('Add media');
    $session->wait(3000);
    $this->assertNotEmpty($assert->waitForText('Add or select media'));
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));

    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_featured_highlight[0][headline]' => 'Reusable block original',
    ], 'Save');
    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been created.');

    $this->drupalGet('node/' . $original_id . '/layout');
    // Add a new inline block.
    $this->clickLink('Add block');
    $assert->waitForText('Create custom block');
    $this->clickLink('Create custom block');
    $assert->waitForText('Add a new Inline Block');
    $this->clickLink('Featured Highlight');
    // Verify that the add block has been opened in the modal.
    $assert->waitForText('Block description');
    $page->fillField('settings[label]', 'An inline block');
    $page->fillField('settings[block_form][field_block_featured_highlight][0][headline]', 'Inline block original');
    $page->pressButton('Add block');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('The layout override has been saved.');

    // Add a reusable block.
    $this->drupalGet('node/' . $original_id . '/layout');
    $this->clickLink('Add block');
    $assert->waitForText('Create custom block');
    $this->clickLink('Reusable block test');
    $assert->waitForText('Block description');
    $page->pressButton('Add block');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('The layout override has been saved.');
    $assert->pageTextContains('Reusable block original');

    // Make a revision to the inline block.
    $this->drupalGet('node/' . $original_id . '/layout');
    $this->clickContextualLink('.block-inline-blockutexas-featured-highlight', 'Configure');
    $assert->waitForText('Block description');
    $page->fillField('settings[block_form][field_block_featured_highlight][0][headline]', 'Inline block first revision');
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('The layout override has been saved.');
    $assert->pageTextContains('Inline block first revision');

    $this->drupalGet('entity_clone/node/' . $original_id);
    $page->pressButton('Clone');
    $assert->pageTextContains('Test Flex Page');
    $assert->pageTextContains('Inline block first revision');
    $assert->pageTextContains('Reusable block original');

    $this->clickLink('Layout');
    $this->clickContextualLink('.block-inline-blockutexas-featured-highlight', 'Configure');
    $assert->waitForText('Block description');
    $page->fillField('settings[block_form][field_block_featured_highlight][0][headline]', 'Inline block revision to clone');
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('Inline block revision to clone');

    $this->drupalGet('node/' . $original_id);
    $assert->pageTextContains('Inline block first revision');
    $this->clickLink('Layout');
    $this->clickContextualLink('.block-inline-blockutexas-featured-highlight', 'Configure');
    $assert->waitForText('Block description');
    $page->fillField('settings[block_form][field_block_featured_highlight][0][headline]', 'Inline block revision to original');
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
    $assert->pageTextContains('Inline block revision to original');

    $this->drupalGet('node/' . $clone_id);
    $assert->pageTextContains('Inline block revision to clone');

    // Update the reusable block.
    $this->drupalGet('admin/structure/block/block-content');
    $this->clickLink('Reusable block test');
    $this->submitForm([
      'info[0][value]' => $block_name,
      'field_block_featured_highlight[0][headline]' => 'Reusable block revision',
    ], 'Save');
    $assert->pageTextContains($block_type . ' ' . $block_name . ' has been updated.');

    $this->drupalGet('node/' . $original_id);
    $assert->pageTextContains('Reusable block revision');
    $this->drupalGet('node/' . $clone_id);
    $assert->pageTextContains('Reusable block revision');

    // Delete the original node, run cron, then
    // verify the cloned node content persists.
    $node = Node::load($original_id);
    $node->delete();
    // It exists before cron run.
    $this->drupalGet('node/' . $clone_id);
    $assert->pageTextContains('Inline block revision to clone');
    $this->cronRun();
    // It exists after cron run.
    $this->drupalGet('node/' . $clone_id);
    $assert->pageTextContains('Inline block revision to clone');
  }

}
