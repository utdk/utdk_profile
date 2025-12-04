<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\Tests\utexas\Traits\TextFormatsTestTraits\TextFormatsTestTrait;

/**
 * Verifies Flex HTML behavior.
 */
class TextFormatsTest extends FunctionalJavascriptTestBase {

  use TextFormatsTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->testContentEditorUser);
  }

  /**
   * Test behavior of existing text formats.
   */
  public function testTextFormats() {
    $this->verifyRestrictedHtmlAllowedTags();
    $this->verifyBasicHtmlAllowedTags();
    $this->verifyFullHtmlAllowedTags();
    $this->verifyFlexHtmlSourceEditing();
    $this->verifyQualtricsFilterOutput();
  }

}
