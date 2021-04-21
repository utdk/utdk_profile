<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\node\Entity\Node;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Verifies background colors/images can be added to sections.
 *
 * @group utexas
 */
class BackgroundAccentTest extends WebDriverTestBase {
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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeContentEditor();
    $this->drupalLogin($this->testUser);
  }

  /**
   * Initial action for all background tests.
   */
  public function testSectionBackgrounds() {
    // Add an image to the Media Library.
    $this->testImage = $this->createTestMediaImage();

    $this->getSession()->resizeWindow(900, 2000);

    $this->backgroundImage();
    $this->backgroundColors();
  }

  /**
   * Test background color configuration.
   */
  public function backgroundImage() {
    $node = Node::create([
      'type'        => 'utexas_flex_page',
      'title'       => 'Test Flex Page',
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    // Access the section configuration toolbar.
    $this->clickLink('Configure Section 1');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $checkbox_selector = '.layout-builder-configure-section details';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[1]->click();

    // Add a background image.
    $assert->pageTextContains('Background image');
    $settings_selectors = '.layout-builder-configure-section details';
    $settings = $page->findAll('css', $settings_selectors);
    $settings[0]->click();
    $page->pressButton('Add media');
    $this->assertNotEmpty($assert->waitForText('Add or select media'));
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.media-library-item__remove'));

    // Save the section configuration.
    $this->submitForm([], 'Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    // A "background-accent" class is added to the section.
    $assert->elementExists('css', '.layout-builder__layout.background-accent');
    $actual_background_image = $this->getSession()->evaluateScript('jQuery(".layout-builder__layout.background-accent div").css("background-image")');
    // The background image style matches the uploaded image.
    $this->assertStringContainsString("utexas_image_style_1600w_500h/public/image-test.png", $actual_background_image);
    // There is no blur effect initially.
    $actual_filter = $this->getSession()->evaluateScript('jQuery(".layout-builder__layout.background-accent div").css("filter")');
    $this->assertSame('none', $actual_filter);
    $page->pressButton('Save layout');

    // Add a blur effect.
    $this->clickLink('Layout');
    // Access the section configuration toolbar.
    $this->clickLink('Configure Section 1');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $assert->pageTextContains('Background image');
    $settings[0]->click();
    $this->getSession()->getPage()->checkField("layout_settings[background-accent-wrapper][blur]");
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));

    $this->clickLink('Add block');
    $this->assertNotEmpty($assert->waitForText('Create custom block'));
    $this->clickLink('Header Menu');
    $this->assertNotEmpty($assert->waitForText('Configure block'));
    $page->pressButton('Add block');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.layout__region--main h2.ut-headline'));
    $actual_filter = $this->getSession()->evaluateScript('jQuery(".layout-builder__layout.background-accent div").css("filter")');
    // Blur is present.
    $this->assertSame('blur(5px)', $actual_filter);

    // Cleanup.
    $node->delete();
  }

  /**
   * Test background color configuration.
   */
  public function backgroundColors() {
    $node = Node::create([
      'type'        => 'utexas_flex_page',
      'title'       => 'Test Flex Page',
    ]);
    $node->save();
    $this->drupalGet('/node/' . $node->id());
    $this->clickLink('Layout');
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    // The available hex colors & their corresponding rgb values.
    $color_palette = [
      '074d6a' => 'rgb(7, 77, 106)',
      '138791' => 'rgb(19, 135, 145)',
      'f9fafb' => 'rgb(249, 250, 251)',
      'e6ebed' => 'rgb(230, 235, 237)',
      'c4cdd4' => 'rgb(196, 205, 212)',
      '7d8a92' => 'rgb(125, 138, 146)',
      '5e686e' => 'rgb(94, 104, 110)',
      '3e4549' => 'rgb(62, 69, 73)',
      '487d39' => 'rgb(72, 125, 57)',
      '9d4700' => 'rgb(157, 71, 0)',
      'ebeced' => 'rgb(235, 236, 237)',
      'c2c5c8' => 'rgb(194, 197, 200)',
      '858c91' => 'rgb(133, 140, 145)',
      '1f262b' => 'rgb(31, 38, 43)',
      'fbfbf9' => 'rgb(251, 251, 249)',
      'f2f1ed' => 'rgb(242, 241, 237)',
      'e6e4dc' => 'rgb(230, 228, 220)',
      'aba89e' => 'rgb(171, 168, 158)',
      '807e76' => 'rgb(128, 126, 118)',
      '56544e' => 'rgb(86, 84, 78)',
    ];

    $this->clickLink('Configure Section 1');
    $this->assertNotEmpty($assert->waitForText('Section width'));

    $checkbox_selector = '.layout-builder-configure-section details';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[1]->click();

    // Verify all colors present.
    foreach (array_keys($color_palette) as $hex) {
      $assert->elementExists('css', 'input[value="' . $hex . '"]');
    }

    $this->getSession()->getPage()->selectFieldOption("layout_settings[background-color-wrapper][background-color]", "none");
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    $this->clickLink('Add block');
    $this->assertNotEmpty($assert->waitForText('Create custom block'));
    $this->clickLink('Header Menu');
    $this->assertNotEmpty($assert->waitForText('Configure block'));
    $page->pressButton('Add block');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.layout__region--main h2.ut-headline'));
    $page->pressButton('Save layout');
    // When "none" is selected, no CSS is added.
    $assert->elementNotExists('css', '.layout-builder__layout.utexas-bg-none');
    $actual_background = $this->getSession()->evaluateScript('jQuery(".layout-builder__layout").css("background-color")');
    $this->assertSame(NULL, $actual_background);

    $this->clickLink('Layout');
    $this->clickLink('Configure Section 1');
    $this->assertNotEmpty($assert->waitForText('Section width'));
    $checkbox_selector = '.layout-builder-configure-section details';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[1]->click();
    $this->assertNotEmpty($assert->waitForText('Bluebonnet'));
    // Verify rendering of only one color.
    $this->verifyBgColor('56544e', $color_palette['56544e'], $assert, $page);

    // White background is added to menu block automatically.
    $menu_nav_background_color = $this->getSession()->evaluateScript('jQuery(".background-accent nav").css("background-color")');
    $this->assertSame("rgb(255, 255, 255)", $menu_nav_background_color);

    // White background is added automatically when a generic block is placed.
    $this->clickLink('Add block');
    $this->assertNotEmpty($assert->waitForText('Create custom block'));
    $this->clickLink('Recent content');
    $this->assertNotEmpty($assert->waitForText('Configure block'));
    $page->pressButton('Add block');
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.background-accent div.block-views-blockcontent-recent-block-1'));
    $basic_block_background_color = $this->getSession()->evaluateScript('jQuery(".background-accent div.block-views-blockcontent-recent-block-1").css("background-color")');
    $this->assertSame("rgb(255, 255, 255)", $basic_block_background_color);

    // Cleanup.
    $node->delete();
  }

  /**
   * Helper function for iterating over color tests.
   */
  private function verifyBgColor($input_hex, $expected_rgb, $assert, $page) {
    $this->getSession()->getPage()->selectFieldOption("layout_settings[background-color-wrapper][background-color]", $input_hex);
    $page->pressButton('Update');
    $this->assertNotEmpty($assert->waitForText('You have unsaved changes'));
    // A "background" class is added to the section. The correct color is found.
    $this->assertNotEmpty($assert->waitForElementVisible('css', '.layout-builder__layout.utexas-bg-' . $input_hex));
    $actual_background = $this->getSession()->evaluateScript('jQuery(".background-accent.utexas-bg-' . $input_hex . '").css("background-color")');
    $this->assertSame($expected_rgb, $actual_background);
  }

}
