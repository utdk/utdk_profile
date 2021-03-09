<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies that Layout Builder behaves as configured.
 */
trait LayoutBuilderIntegrationTestTrait {

  /**
   * Menu Blocks are not duplicated in the settings tray.
   */
  public function verifyNoDuplicateMenuBlocks() {
    $assert = $this->assertSession();
    $session = $this->getSession();
    $page = $session->getPage();
    $this->drupalGet("/node/add/utexas_flex_page");
    $edit = [
      'title[0][value]' => 'Layout Builder Test',
    ];
    $this->submitForm($edit, 'Save');
    $node = $this->drupalGetNodeByTitle('Layout Builder Test');
    $this->drupalGet('node/' . $node->id() . '/layout');
    $this->clickLink('Add block');
    $this->assertNotEmpty($assert->waitForText('Create custom block'));

    // Find all <a> links with text "Header Menu" in the settings tray.
    $headerMenuLink = $page->findAll('xpath', '//*[@id="drupal-off-canvas"]//a[text()="Header Menu"]');

    // There is only one link with text "Header Menu".
    $this->assertEquals(1, count($headerMenuLink));

    // Clean configuration introduced by test.
    $node = $this->drupalGetNodeByTitle('Layout Builder Test');
    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->submitForm([], 'Delete');
  }

}
