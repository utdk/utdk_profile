<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

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
  protected function setUp(): void {
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
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();
    $this->getSession()->resizeWindow(900, 2000);
    $flex_page_id = $this->createFlexPage();

    // Test programmatically changing Layout Builder Style setting, since
    // typical Site Managers will not have this permission.
    \Drupal::service('config.factory')->getEditable('layout_builder_styles.settings')->set('form_type', 'multiple-select')->save();

    $this->drupalGet('/node/' . $flex_page_id);
    $this->clickLink('Layout');
    $this->clickLink('Configure Section 1');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    // A "container" class is added to the section by default.
    // The one-column layout defaults to "readable" width.
    $assert->elementExists('css', '.layout-builder__layout.container.readable');
    $assert->elementNotExists('css', '.layout-builder__layout.container-fluid');
    // The page title gets set to "readable" width, too.
    $assert->elementExists('css', '.block-page-title-block.utexas-readable');

    // Set the section to "Full width of page".
    $assert->elementExists('css', 'select[name="layout_settings[section_width]"] option[value="container-fluid"]');
    $this->getSession()->getPage()->selectFieldOption("layout_settings[section_width]", "container-fluid", TRUE);
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    // A "container-fluid" class is added to the section.
    $assert->elementNotExists('css', '.layout-builder__layout.container.readable');
    $assert->elementExists('css', '.layout-builder__layout.container-fluid');
    $page->pressButton('Save layout');
    // The page title does not get set to "readable" width.
    $assert->elementNotExists('css', '.block-page-title-block.utexas-readable');

    // Border with background.
    $assert->elementNotExists('css', '.utexas-field-border.utexas-field-background');
    $this->drupalGet('/node/' . $flex_page_id);
    $this->clickLink('Layout');
    $this->clickLink('Add block');
    $this->assertNotEmpty($assert->waitForText('Create custom block'));
    $this->clickLink('Recent content');
    $this->assertNotEmpty($assert->waitForText('Configure block'));
    // Select style checkbox (by clicking the label) in block configuration.
    $assert->elementExists('css', 'input[name="layout_builder_style_utexas_borders[utexas_border_with_background]"] + label')->click();
    $page->pressButton('Add block');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.block-views-blockcontent-recent-block-1'));
    // Border & background classes are added to the section.
    $assert->elementExists('css', '.utexas-field-border.utexas-field-background');

    // Border without background.
    $assert->elementNotExists('css', '.utexas-field-border.utexas-centered-headline');
    $this->drupalGet('/node/' . $flex_page_id);
    $this->clickLink('Layout');
    $this->clickLink('Add block');
    $this->assertNotEmpty($assert->waitForText('Create custom block'));
    $this->clickLink('Recent content');
    $this->assertNotEmpty($assert->waitForText('Configure block'));
    // Select style checkbox (by clicking the label) in block configuration.
    $assert->elementExists('css', 'input[name="layout_builder_style_utexas_borders[utexas_border_without_background]"] + label')->click();
    $page->pressButton('Add block');
    // Border & background classes are added to the section.
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-field-border.utexas-centered-headline'));

    // No padding between columns.
    $assert->elementNotExists('css', '.utexas-layout-no-padding');
    $this->drupalGet('/node/' . $flex_page_id);
    $this->clickLink('Layout');
    $this->clickLink('Configure Section 1');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    // Set the section to "No padding".
    // Select style checkbox (by clicking the label) in section configuration.
    $assert->elementExists('css', 'input[name="layout_builder_style_utexas_section_margins_padding[utexas_no_padding]"] + label')->click();
    $page->pressButton('Update');
    $assert->statusMessageContainsAfterWait('You have unsaved changes');
    // A "utexas-layout-no-padding" class is added to the section.
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.utexas-layout-no-padding'));
  }

}
