<?php

namespace Drupal\Tests\utexas\Traits;

use Drupal\node\Entity\Node;

/**
 * Verifies that Flex HTML text format works as expected.
 */
trait FlexHTMLTestTrait {

  /**
   * Qualtrics Filter renders as expected.
   */
  public function verifyQualtricsFilterOutput() {
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();
    // Create node object with Qualtrics embed syntax.
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Qualtrics Test',
      'body' => [
        'value' => '[embed]https://utexas.qualtrics.com/SE/?SID=SV_af1Gk9JWK2khAEJ | height:500 | title:test[/embed]',
        'format' => 'flex_html',
      ],
    ]);
    $basic_page_id = $node->save();
    $this->drupalGet('node/' . $basic_page_id);
    $expected_src = 'https://utexas.qualtrics.com/SE/?SID=SV_af1Gk9JWK2khAEJ';
    $assert->elementAttributeContains('css', '.region-content iframe', 'src', $expected_src);
  }

}
