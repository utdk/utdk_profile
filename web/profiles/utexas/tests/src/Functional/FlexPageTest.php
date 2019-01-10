<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\FlexPageRevisionsTestTrait;
use Drupal\Tests\utexas\Traits\WysiwygTestTrait;

/**
 * Verifies Flex Page nodes revisions work without issue.
 *
 * @group utexas
 */
class FlexPageTest extends BrowserTestBase {
  use EntityTestTrait;
  use UserTestTrait;
  use InstallTestTrait;
  use FlexPageRevisionsTestTrait;
  use WysiwygTestTrait;

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
  public function testFlexPage() {
    $this->verifyRevisioning();
    $this->verifyWysiwyg();
  }

}
