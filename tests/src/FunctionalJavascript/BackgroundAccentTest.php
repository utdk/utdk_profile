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
  protected function setUp() {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeContentEditor();
    $this->drupalLogin($this->testUser);
  }

  /**
   * Test background color configuration.
   */
  public function testBackgroundImage() {
    // Add an image to the Media Library.
    $this->testImage = $this->createTestMediaImage();

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

    // Access the section configuration toolbar.
    $this->clickLink('Configure Section 1');
    $assert->assertWaitOnAjaxRequest();
    $checkbox_selector = '.layout-builder-configure-section details';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[1]->click();

    // Add a background image.
    $assert->pageTextContains('Background image');
    $settings_selectors = '.layout-builder-configure-section details';
    $settings = $page->findAll('css', $settings_selectors);
    $settings[0]->click();
    $page->pressButton('Set media');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Add or select media');
    $assert->pageTextContains('Image 1');
    // Select the first media item (should be "Image 1").
    $checkbox_selector = '.media-library-view .js-click-to-select-checkbox input';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[0]->click();
    $assert->elementExists('css', '.ui-dialog-buttonset')->pressButton('Insert selected');
    $assert->assertWaitOnAjaxRequest();

    // Save the section configuration.
    $this->submitForm([], 'Update');
    $assert->assertWaitOnAjaxRequest();
    // A "background-accent" class is added to the section.
    $assert->elementExists('css', '.layout-builder__layout.background-accent');
    $actual_background_image = $this->getSession()->evaluateScript('jQuery(".layout-builder__layout.background-accent div").css("background-image")');
    // The background image style matches the uploaded image.
    $this->assertContains("utexas_image_style_1600w_500h/public/image-test.png", $actual_background_image);
    // There is no blur effect initially.
    $actual_filter = $this->getSession()->evaluateScript('jQuery(".layout-builder__layout.background-accent div").css("filter")');
    $this->assertSame('none', $actual_filter);

    // Add a blur effect.
    // Access the section configuration toolbar.
    $this->clickLink('Configure Section 1');
    $assert->assertWaitOnAjaxRequest();
    $checkbox_selector = '.layout-builder-configure-section details';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[1]->click();
    $assert->pageTextContains('Background image');
    $settings_selectors = '.layout-builder-configure-section details';
    $settings = $page->findAll('css', $settings_selectors);
    $settings[0]->click();
    $edit = ['layout_settings[background-accent-wrapper][blur]' => "1"];
    $this->submitForm($edit, 'Update');
    $assert->assertWaitOnAjaxRequest();
    $actual_filter = $this->getSession()->evaluateScript('jQuery(".layout-builder__layout.background-accent div").css("filter")');
    // Blur is present.
    $this->assertSame('blur(5px)', $actual_filter);

  }

  /**
   * Test background color configuration.
   */
  public function testBackgroundColors() {
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

    $this->clickLink('Configure Section 1');
    $assert->assertWaitOnAjaxRequest();

    $checkbox_selector = '.layout-builder-configure-section details';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[1]->click();

    $edit = ['layout_settings[background-color-wrapper][background-color]' => "none"];
    $this->submitForm($edit, 'Update');
    $assert->assertWaitOnAjaxRequest();
    // A "background" class is added to the section. The correct color is found.
    $assert->elementNotExists('css', '.layout-builder__layout.utexas-bg-none');
    $actual_background = $this->getSession()->evaluateScript('jQuery(".layout-builder__layout").css("background-color")');
    $this->assertSame("rgba(0, 0, 0, 0)", $actual_background);

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
    foreach ($color_palette as $hex => $rgb) {
      $this->verifyBgColor($hex, $rgb, $assert, $page);
    }
  }

  /**
   * Helper function for iterating over color tests.
   */
  private function verifyBgColor($input_hex, $expected_rgb, $assert, $page) {
    $this->clickLink('Configure Section 1');
    $assert->assertWaitOnAjaxRequest();

    $checkbox_selector = '.layout-builder-configure-section details';
    $checkboxes = $page->findAll('css', $checkbox_selector);
    $checkboxes[1]->click();

    $edit = ['layout_settings[background-color-wrapper][background-color]' => $input_hex];
    $this->submitForm($edit, 'Update');
    $assert->assertWaitOnAjaxRequest();
    // A "background" class is added to the section. The correct color is found.
    $assert->elementExists('css', '.layout-builder__layout.utexas-bg-' . $input_hex);
    $actual_background = $this->getSession()->evaluateScript('jQuery(".background-accent.utexas-bg-' . $input_hex . '").css("background-color")');
    $this->assertSame($expected_rgb, $actual_background);
  }

}
