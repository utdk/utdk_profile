<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\node\Entity\Node;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Verify functionality of 1, 2, 3, and 4 column layouts.
 *
 * @group utexas
 */
class LayoutsTest extends WebDriverTestBase {
  use EntityTestTrait;
  use InstallTestTrait;
  use TestFileCreationTrait;
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
    $this->initializeContentEditor();
    $this->drupalLogin($this->testUser);
    $this->testImage = $this->createTestMediaImage();
    $this->testVideo = $this->createTestMediaVideoExternal();
  }

  /**
   * Initial action for all layout tests.
   */
  public function testLayouts() {
    // Create a node & remove default section.
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->getSession()->resizeWindow(900, 2000);
    $node = Node::create([
      'type'        => 'utexas_flex_page',
      'title'       => 'Test Flex Page',
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    // Remove existing one column layout.
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    $this->oneColumnLayout();
    $this->twoColumnLayout();
    $this->threeColumnLayout();
    $this->fourColumnLayout();
  }

  /**
   * One column functionality.
   */
  public function oneColumnLayout() {
    $this->clickLink('Layout');
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('One column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    // Verify Background accent & color options available.
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="background-accent-media-library-open-button-layout_settings-background-accent-wrapper"]');
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForText('Configure Section 1'));
    $assert->elementExists('css', '.layout--utexas-onecol');
    // Cleanup.
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
  }

  /**
   * Two column functionality.
   */
  public function twoColumnLayout() {
    $this->clickLink('Layout');
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Two column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="background-accent-media-library-open-button-layout_settings-background-accent-wrapper"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="50-50"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="33-67"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="67-33"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="25-75"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="75-25"]');

    // 50-50 ratio generates expected CSS.
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "50%/50%");
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--twocol.utexas-layout--twocol--50-50'));
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    // 33-67 ratio generates expected CSS.
    $this->clickLink('Layout');
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Two column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "33%/67%");
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--twocol.utexas-layout--twocol--33-67'));
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    // 67-33 ratio generates expected CSS.
    $this->clickLink('Layout');
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Two column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "67%/33%");
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--twocol.utexas-layout--twocol--67-33'));
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    // 25-75 ratio generates expected CSS.
    $this->clickLink('Layout');
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Two column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "25%/75%");
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--twocol.utexas-layout--twocol--25-75'));
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    // 75-25 ratio generates expected CSS.
    $this->clickLink('Layout');
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Two column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "75%/25%");
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--twocol.utexas-layout--twocol--75-25'));
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
  }

  /**
   * Three column functionality.
   */
  public function threeColumnLayout() {
    $this->clickLink('Layout');
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Three column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="background-accent-media-library-open-button-layout_settings-background-accent-wrapper"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="25-50-25"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="33-34-33"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="50-25-25"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="25-25-50"]');

    // 25-50-25 ratio generates expected CSS.
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "25%/50%/25%");
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--threecol.utexas-layout--threecol--25-50-25'));
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    // 33-34-33 ratio generates expected CSS.
    $this->clickLink('Layout');
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Three column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "33%/34%/33%");
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--threecol.utexas-layout--threecol--33-34-33'));
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    // 50-25-25 ratio generates expected CSS.
    $this->clickLink('Layout');
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Three column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "50%/25%/25%");
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--threecol.utexas-layout--threecol--50-25-25'));
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');

    // 25-25-50 ratio generates expected CSS.
    $this->clickLink('Layout');
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Three column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "25%/25%/50%");
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--threecol.utexas-layout--threecol--25-25-50'));
    $this->clickLink('Remove Section 1');
    $this->assertNotEmpty($assert->waitForText('Are you sure you want to remove'));
    $page->pressButton('Remove');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $page->pressButton('Save layout');
  }

  /**
   * Four column functionality.
   */
  public function fourColumnLayout() {
    $this->clickLink('Layout');
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->clickLink('Add section');
    $this->assertNotEmpty($assert->waitForText('Choose a layout for this section'));
    $this->clickLink('Four column');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    // Background color & accent are present.
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="background-accent-media-library-open-button-layout_settings-background-accent-wrapper"]');

    // Fourcol class present.
    $page->pressButton('Add section');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout--fourcol'));
  }

}
