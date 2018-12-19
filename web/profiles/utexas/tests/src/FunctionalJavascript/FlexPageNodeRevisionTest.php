<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Flex Page nodes revisions work without issue.
 *
 * @group utexas
 */
class FlexPageNodeRevisionTest extends WebDriverTestBase {
  use EntityTestTrait;
  use UserTestTrait;
  use InstallTestTrait;
  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';
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
    $this->initializeFlexPageEditor();
  }

  /**
   * Test output.
   */
  public function testOutput() {
    // Generate a test node for testing that revisions can be accessed.
    $basic_page_id = $this->createBasicPage();
    $this->assertAllowed("/node/add/utexas_flex_page");
    // // 1. Add Node title and revision information.
    $edit = [
      'title[0][value]' => 'Revision Test',
      'edit-revision-log-0-value' => 'First revision',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $node = $this->drupalGetNodeByTitle('Revision Test');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // 2. Edit the node to create a new revision.
    $this->drupalPostForm(NULL, [
      'title[0][value]' => 'Revision Test rev2',
      'edit-revision-log-0-value' => 'Second revision',
    ],
      'edit-submit');
    $node = $this->drupalGetNodeByTitle('Revision Test rev2');
    $this->drupalGet('node/' . $node->id() . '/revisions/' . $node->getRevisionId() . '/view');
    // 3. Verify Revision 1 title, is present.
    $this->assertRaw('Revision Test');

    // Sign out!
    $this->drupalLogout();
  }

}
