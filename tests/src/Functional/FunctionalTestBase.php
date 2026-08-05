<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use Drupal\Core\Database\Database;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Base class for Functional tests.
 *
 * Slimmed down after a review found EntityTestTrait/UserTestTrait,
 * $testContentEditorUser/$testSiteManagerUser, and assertAllowed()/
 * assertForbidden() had no remaining consumers — every test that used
 * them was either fully browser-based (moved to
 * tests/playwright/SiteAnnouncementTest.php and SocialLinksTest.php, then
 * deleted here) or didn't use them at all (ReadOnlyTest.php, the one
 * remaining subclass, only reads config directly). Deliberately not
 * re-adding assertAllowed()/assertForbidden(): that's exactly the
 * browser-testing pattern this profile's tests are moving to Playwright
 * for — see tests/playwright/BaseInstallationTest.php's class docblock.
 */
abstract class FunctionalTestBase extends BrowserTestBase {

  use InstallTestTrait;

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
  protected $defaultTheme = 'speedway';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->utexasSharedSetup();
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  protected function cleanupEnvironment() {
    // We override the cleanupEnvironment method from core's BrowserTestBase
    // due to issues with Docker Desktop. See below.
    // Remove all prefixed tables.
    $original_connection_info = Database::getConnectionInfo('simpletest_original_default');
    $original_prefix = $original_connection_info['default']['prefix'];
    $test_connection_info = Database::getConnectionInfo('default');
    $test_prefix = $test_connection_info['default']['prefix'];
    if ($original_prefix != $test_prefix) {
      $tables = Database::getConnection()->schema()->findTables('%');
      foreach ($tables as $table) {
        if (Database::getConnection()->schema()->dropTable($table)) {
          unset($tables[$table]);
        }
      }
    }

    // We skip the following, which causes issues when used with
    // Docker Desktop. Instead, the test execution command will perform the
    // cleanup. See utdk_localdev#99.
    // @codingStandardsIgnoreLine
    // \Drupal::service('file_system')->deleteRecursive($this->siteDirectory, [$this, 'filePreDeleteCallback']);
  }

}
