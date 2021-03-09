<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies that Flex Pages can be revised in a Drupal way.
 */
trait FlexPageRevisionsTestTrait {

  /**
   * Test that revisioning works per Drupal convention.
   */
  public function verifyRevisioning() {
    // Generate a test node for testing that revisions can be accessed.
    $this->assertAllowed("/node/add/utexas_flex_page");
    // // 1. Add Node title and revision information.
    $edit = [
      'title[0][value]' => 'Revision Test',
      'edit-revision-log-0-value' => 'First revision',
    ];
    $this->submitForm($edit, 'Save');
    $node = $this->drupalGetNodeByTitle('Revision Test');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // 2. Edit the node to create a new revision.
    $this->submitForm([
      'title[0][value]' => 'Revision Test rev2',
      'edit-revision-log-0-value' => 'Second revision',
    ],
      'Save');
    $node = $this->drupalGetNodeByTitle('Revision Test rev2');
    $this->drupalGet('node/' . $node->id() . '/revisions/' . $node->getRevisionId() . '/view');
    $this->assertSession()->statusCodeEquals(200);
    // 3. Verify Revision 1 title, is present.
    $this->assertRaw('Revision Test');

    // Clean configuration introduced by test.
    $node = $this->drupalGetNodeByTitle('Revision Test rev2');
    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->submitForm([], 'Delete');
  }

}
