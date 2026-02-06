<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

/**
 * Verifies custom compound field schema, validation, & output.
 */
class LayoutBuilderIntegrationTest extends FunctionalJavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->copyTestFiles();
    $this->drupalLogin($this->testSiteManagerUser);
  }

  /**
   * Test Layout Builder integration.
   */
  public function testLayoutBuilderIntegration() {
    /** @var \Drupal\Tests\utexas\FunctionalJavascript\WidgetsTest $this */
    /** @var \Drupal\FunctionalJavascriptTests\WebDriverWebAssert $assert */
    $assert = $this->assertSession();
    /** @var \Behat\Mink\Element\DocumentElement $page */
    $page = $this->getSession()->getPage();

    // CRUD: CREATE.
    $flex_page_id = $this->createFlexPage();

    $this->drupalGetNodeLayoutTab($flex_page_id);
    $this->clickLink('Add block');
    $this->assertTrue($assert->waitForText('Create content block'));

    // Find all <a> links with text "Header Menu" in the settings tray.
    $headerMenuLink = $page->findAll('xpath', '//*[@id="drupal-off-canvas"]//a[text()="Header Menu"]');

    // There is only one link with text "Header Menu".
    $this->assertEquals(1, count($headerMenuLink));
  }

}
