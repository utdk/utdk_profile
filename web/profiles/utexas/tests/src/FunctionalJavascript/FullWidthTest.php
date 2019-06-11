<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\node\Entity\Node;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Verifies custom compound field schema, validation, & output.
 *
 * @group utexas
 */
class FullWidthTest extends WebDriverTestBase {
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
  public function testCustomWidgets() {
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
    $this->clickLink('Add Section');
    $assert->assertWaitOnAjaxRequest();
    $this->clickLink('One column');
    $assert->assertWaitOnAjaxRequest();
    $page->pressButton('Add section');
    $assert->assertWaitOnAjaxRequest();
    // A "container" class is added to the section by default.
    $assert->elementExists('css', '.layout-builder__layout.container');
    $assert->elementNotExists('css', '.layout-builder__layout.container-fluid');
    // Set the section to "Full width of page".
    $this->clickLink('Configure section');
    $assert->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->selectFieldOption("layout_builder_style", "full_width_of_page");
    $page->pressButton('Update');
    $assert->assertWaitOnAjaxRequest();
    // A "container-fluid" class is added to the section.
    $assert->elementExists('css', '.layout-builder__layout.container-fluid');
  }

}
