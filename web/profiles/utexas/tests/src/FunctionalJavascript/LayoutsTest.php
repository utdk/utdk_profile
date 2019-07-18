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
  protected function setUp() {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeFlexPageEditor();
    $this->drupalLogin($this->testUser);
    $this->testImage = $this->createTestMediaImage();
    $this->testVideo = $this->createTestMediaVideoExternal();
  }

  /**
   * One column functionality.
   */
  public function test1ColumnLayout() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->getSession()->resizeWindow(900, 2000);
    $node = Node::create([
      'type'        => 'utexas_flex_page',
      'title'       => 'Test Flex Page',
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id());

    // Four column functionality.
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    // Remove existing one column layout.
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // Background color & accent are present.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('One column');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="layout_settings-media-library-open-button-layout_settings-background-accent-wrapper-background-accent"]');

    // Fourcol class present.
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.layout--utexas-onecol');
  }

  /**
   * Two column functionality.
   */
  public function test2ColumnLayout() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->getSession()->resizeWindow(900, 2000);
    $node = Node::create([
      'type'        => 'utexas_flex_page',
      'title'       => 'Test Flex Page',
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id());

    // Two column functionality.
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    // Remove existing one column layout.
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // Background color & accent are present.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Two column');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="layout_settings-media-library-open-button-layout_settings-background-accent-wrapper-background-accent"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="50-50"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="33-67"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="67-33"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="25-75"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="75-25"]');

    // 50-50 ratio generates expected CSS.
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "50%/50%");
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--twocol.utexas-layout--twocol--50-50');
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // 33-67 ratio generates expected CSS.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Two column');
    $assert->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "33%/67%");
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--twocol.utexas-layout--twocol--33-67');
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // 67-33 ratio generates expected CSS.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Two column');
    $assert->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "67%/33%");
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--twocol.utexas-layout--twocol--67-33');
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // 25-75 ratio generates expected CSS.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Two column');
    $assert->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "25%/75%");
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--twocol.utexas-layout--twocol--25-75');
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // 75-25 ratio generates expected CSS.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Two column');
    $assert->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "75%/25%");
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--twocol.utexas-layout--twocol--75-25');
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');
  }

  /**
   * Three column functionality.
   */
  public function test3ColumnLayout() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->getSession()->resizeWindow(900, 2000);
    $node = Node::create([
      'type'        => 'utexas_flex_page',
      'title'       => 'Test Flex Page',
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id());

    // Two column functionality.
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    // Remove existing one column layout.
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // Background color & accent are present.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Three column');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="layout_settings-media-library-open-button-layout_settings-background-accent-wrapper-background-accent"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="25-50-25"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="33-34-33"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="50-25-25"]');
    $assert->elementExists('css', 'select[name="layout_settings[column_widths]"] option[value="25-25-50"]');

    // 25-50-25 ratio generates expected CSS.
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "25%/50%/25%");
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--threecol.utexas-layout--threecol--25-50-25');
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // 33-34-33 ratio generates expected CSS.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Three column');
    $assert->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "33%/34%/33%");
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--threecol.utexas-layout--threecol--33-34-33');
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // 50-25-25 ratio generates expected CSS.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Three column');
    $assert->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "50%/25%/25%");
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--threecol.utexas-layout--threecol--50-25-25');
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // 25-25-50 ratio generates expected CSS.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Three column');
    $assert->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption("layout_settings[column_widths]", "25%/25%/50%");
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--threecol.utexas-layout--threecol--25-25-50');
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');
  }

  /**
   * Four column functionality.
   */
  public function test4ColumnLayout() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    $this->getSession()->resizeWindow(900, 2000);
    $node = Node::create([
      'type'        => 'utexas_flex_page',
      'title'       => 'Test Flex Page',
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id());

    // Four column functionality.
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    // Remove existing one column layout.
    $this->clickLink('Remove section');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Remove');

    // Background color & accent are present.
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Four column');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', 'input[name="layout_settings[background-color-wrapper][background-color]"]');
    $assert->elementExists('css', 'input[name="layout_settings-media-library-open-button-layout_settings-background-accent-wrapper-background-accent"]');

    // Fourcol class present.
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', '.utexas-layout--fourcol');
  }

}
