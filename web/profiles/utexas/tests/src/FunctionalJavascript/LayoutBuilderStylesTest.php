<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\node\Entity\Node;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Verifies default Layout Builder Styles are present & add expected classes.
 *
 * @group utexas
 */
class LayoutBuilderStylesTest extends WebDriverTestBase {
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
   * Test any custom widgets sequentially, using the same installation.
   */
  public function testStyles() {
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
    $this->clickLink('Configure section');
    $assert->assertWaitOnAjaxRequest();
    // A "container" class is added to the section by default.
    $assert->elementExists('css', '.layout-builder__layout.container');
    $assert->elementNotExists('css', '.layout-builder__layout.container-fluid');
    // Set the section to "Full width of page".
    $this->getSession()->getPage()->selectFieldOption("layout_builder_style", "full_width_of_page");
    $page->pressButton('Update');
    $assert->assertWaitOnAjaxRequest();
    // A "container-fluid" class is added to the section.
    $assert->elementNotExists('css', '.layout-builder__layout.container');
    $assert->elementExists('css', '.layout-builder__layout.container-fluid');

    // Border with background.
    $assert->elementNotExists('css', '.utexas-field-border.utexas-field-background');
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Block');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Recent content');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', 'select[name="layout_builder_style"] option[value="utexas_border_with_background"]');
    $this->getSession()->getPage()->selectFieldOption("layout_builder_style", "utexas_border_with_background");
    $page->pressButton('Add Block');
    $assert->assertWaitOnAjaxRequest();
    // Border & background classes are added to the section.
    $assert->elementExists('css', '.utexas-field-border.utexas-field-background');

    // Border without background.
    $assert->elementNotExists('css', '.utexas-field-border.utexas-centered-headline');
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Add Block');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('Recent content');
    $assert->assertWaitOnAjaxRequest();
    $assert->elementExists('css', 'select[name="layout_builder_style"] option[value="utexas_border_without_background"]');
    $this->getSession()->getPage()->selectFieldOption("layout_builder_style", "utexas_border_without_background");
    $page->pressButton('Add Block');
    $assert->assertWaitOnAjaxRequest();
    // Border & background classes are added to the section.
    $assert->elementExists('css', '.utexas-field-border.utexas-centered-headline');

    // No padding between columns.
    $assert->elementNotExists('css', '.utexas-layout-no-padding');
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    $this->clickLink('Configure section');
    $assert->assertWaitOnAjaxRequest();
    // Set the section to "Full width of page".
    $this->getSession()->getPage()->selectFieldOption("layout_builder_style", "utexas_no_padding");
    $page->pressButton('Update');
    $assert->assertWaitOnAjaxRequest();
    // A "utexas-layout-no-padding" class is added to the section.
    $assert->elementExists('css', '.utexas-layout-no-padding');
  }

}
