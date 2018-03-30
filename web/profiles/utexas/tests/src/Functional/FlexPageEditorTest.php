<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Flex Page Editor role settings in isolateion.
 *
 * @group utexas
 */
class FlexPageEditorTest extends BrowserTestBase {
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
    // @todo: investigate why this addRole apparently needs to happen here,
    // in addition to the one defined in initializeFlexPageEditor @jmf3658.
    $this->testUser->addRole('utexas_flex_page_editor');
    $this->testUser->save();
    $this->drupalLogin($this->testUser);
  }

  /**
   * Tests the Flex Page Editor's access to defined text format filters.
   */
  public function testFormatPermissions() {
    // Make sure that a Flex Page Editor has default access to the
    // Flex HTML format.
    $flex_html = FilterFormat::load('flex_html');
    $this->assertTrue($flex_html->access('use', $this->testUser), 'A Flex Page Editor has default access to the Flex HTML format.');
    // Make sure that a Flex Page Editor doesn't have access to the
    // Full HTML format.
    $full_html = FilterFormat::load('full_html');
    $this->assertFalse($full_html->access('use', $this->testUser), 'A Flex Page Editor does not have access to the Full HTML format.');
    // Verify that 'Flex HTML' is at the top of the filter_formats list.
    $formats = array_keys(filter_formats());
    $this->assertTrue($formats[0] == 'flex_html', 'Flex HTML is at the top of the filter_formats list.');
  }

}
