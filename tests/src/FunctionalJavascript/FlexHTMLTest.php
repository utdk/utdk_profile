<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\Tests\utexas\Traits\FlexHTMLTestTraits\FlexHTMLTestTrait;

/**
 * Verifies Flex HTML behavior.
 *
 * @group utexas
 */
class FlexHTMLTest extends FunctionalJavascriptTestBase {

  use FlexHTMLTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->testContentEditorUser);
  }

  /**
   * Test any FlexHTML settings sequentially, using the same installation.
   */
  public function testFlexHtml() {
    //$this->verifyQualtricsFilterOutput();
  }

}
