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
  protected function setUp() {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeSiteManager();
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

    // Set the configuration to allow multiple styles per block.
    $this->drupalGet('admin/config/content/layout_builder_style/config');
    $page->selectFieldOption('edit-multiselect-multiple', 'multiple');
    $page->selectFieldOption('edit-form-type-multiple-select', 'multiple-select');
    $page->pressButton('Save configuration');

    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    $this->clickLink('Configure Section 1');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    // A "container" class is added to the section by default.
    // The one-column layout defaults to "readable" width.
    $assert->elementExists('css', '.layout-builder__layout.container.readable');
    $assert->elementNotExists('css', '.layout-builder__layout.container-fluid');

    // Set the section to "Full width of page".
    $assert->elementExists('css', 'select[name="layout_settings[section_width]"] option[value="container-fluid"]');
    $this->getSession()->getPage()->selectFieldOption("layout_settings[section_width]", "container-fluid", TRUE);
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    // A "container-fluid" class is added to the section.
    $assert->elementNotExists('css', '.layout-builder__layout.container.readable');
    $assert->elementExists('css', '.layout-builder__layout.container-fluid');

    // Border with background.
    $assert->elementNotExists('css', '.utexas-field-border.utexas-field-background');
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    $this->clickLink('Add block');
    $this->assertNotEmpty($assert->waitForText('Create custom block'));
    $this->clickLink('Recent content');
    $this->assertNotEmpty($assert->waitForText('Configure block'));
    $assert->elementExists('css', 'select[name="layout_builder_style[]"] option[value="utexas_border_with_background"]');
    $this->getSession()->getPage()->selectFieldOption("layout_builder_style[]", "utexas_border_with_background", TRUE);
    $page->pressButton('Add block');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.layout--utexas-onecol h2.ut-headline'));
    // Border & background classes are added to the section.
    $assert->elementExists('css', '.utexas-field-border.utexas-field-background');

    // Border without background.
    $assert->elementNotExists('css', '.utexas-field-border.utexas-centered-headline');
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    $this->clickLink('Add block');
    $this->assertNotEmpty($assert->waitForText('Create custom block'));
    $this->clickLink('Recent content');
    $this->assertNotEmpty($assert->waitForText('Configure block'));
    $assert->elementExists('css', 'select[name="layout_builder_style[]"] option[value="utexas_border_without_background"]');
    $this->getSession()->getPage()->selectFieldOption("layout_builder_style[]", "utexas_border_without_background", TRUE);
    $page->pressButton('Add block');
    // Border & background classes are added to the section.
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-field-border.utexas-centered-headline'));

    // No padding between columns.
    $assert->elementNotExists('css', '.utexas-layout-no-padding');
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    $this->clickLink('Configure Section 1');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    // Set the section to "No padding".
    $assert->elementExists('css', 'select[name="layout_builder_style[]"] option[value="utexas_no_padding"]');
    $this->getSession()->getPage()->selectFieldOption("layout_builder_style[]", "utexas_no_padding", TRUE);
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    // A "utexas-layout-no-padding" class is added to the section.
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout-no-padding'));
  }

}
