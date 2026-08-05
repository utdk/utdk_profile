<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Playwright;

// Composer's autoloader isn't loaded automatically when this test runs
// outside Drupal's own PHPUnit bootstrap (no PSR-4 mapping covers this
// namespace/directory — see PlaywrightHelpersTrait's docblock). Dependencies
// (playwright-php/playwright, etc.) live in the project root's vendor/,
// six directories up from here.
require_once dirname(__DIR__, 6) . '/vendor/autoload.php';
require_once __DIR__ . '/PlaywrightHelpersTrait.php';

use Playwright\Testing\PlaywrightTestCase;

/**
 * Playwright-PHP equivalent of SiteAnnouncementTest.php.
 *
 * Pure browser testing — see BaseInstallationTest.php's class docblock for
 * the full rationale. This whole test is HTTP-access status codes gated by
 * specific permissions, so it has no Drupal-API-only counterpart at all —
 * unlike BaseInstallationTest.php, there's no split Functional test file
 * for this one.
 *
 * The original test creates a brand-new Site Manager account per
 * permission via UserTestTrait::initializeSiteManager($extra_permissions)
 * — drupalCreateUser() builds an ad hoc role holding exactly those extra
 * permissions. Here, the single extra permission is instead granted
 * directly to the shared utexas_site_manager/utexas_content_editor role
 * via the real /admin/people/permissions checkbox matrix
 * (grantPermissionViaUi(), in PlaywrightHelpersTrait), and the same shared
 * account (from ensureRoleAccountsConfigured()) is used for both the
 * "before" (forbidden) and "after" (allowed) checks. This is safe because
 * the base Site Manager/Content Editor permission sets
 * (Drupal\utexas\Permissions::$manager / $editor) don't include any of
 * the three permissions this test grants, and granting one doesn't affect
 * the others, so testing them in sequence on the same account doesn't
 * cross-contaminate results.
 *
 * NOT verified: the original test's setUp() calls
 * copySiteAnnouncementIconFiles() and then edits/deletes an icon named
 * "beacon". No config or code anywhere in this codebase defines a
 * "beacon" icon — it isn't shipped as default config by
 * utexas_site_announcement, and copySiteAnnouncementIconFiles() only
 * copies asset *files*, not icon *config entities*. Where "beacon" comes
 * from in the original test's environment is unclear; if it doesn't exist
 * on the target site, the /icons/beacon/edit and /icons/beacon/delete
 * checks below will 404 instead of behaving as expected. Flagged rather
 * than guessed at.
 */
final class SiteAnnouncementTest extends PlaywrightTestCase {

  use PlaywrightHelpersTrait;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    self::initBaseUrl();
  }

  /**
   * Tests Icons admin pages permissions.
   */
  public function testIcons(): void {
    $this->ensureRoleAccountsConfigured();

    // A Site Manager WITHOUT 'administer utexas announcement icons' cannot
    // access the icon administration pages.
    $this->loginAsPlaywright(self::$siteManagerAccount['name'], self::$siteManagerAccount['password']);
    $this->assertStatusCode('/admin/config/site-announcement/icons', 403);
    $this->assertStatusCode('/admin/config/site-announcement/icons/add', 403);
    // See class docblock: "beacon" is an unverified fixture assumption.
    $this->assertStatusCode('/admin/config/site-announcement/icons/beacon/edit', 403);
    $this->assertStatusCode('/admin/config/site-announcement/icons/beacon/delete', 403);

    // Grant the permission, then confirm access.
    $this->loginAsAdmin();
    $this->grantPermissionViaUi('utexas_site_manager', 'administer utexas announcement icons');
    $this->loginAsPlaywright(self::$siteManagerAccount['name'], self::$siteManagerAccount['password']);
    $this->assertStatusCode('/admin/config/site-announcement/icons', 200);
    $this->assertStatusCode('/admin/config/site-announcement/icons/add', 200);
    $this->assertStatusCode('/admin/config/site-announcement/icons/beacon/edit', 200);
    $this->assertStatusCode('/admin/config/site-announcement/icons/beacon/delete', 200);
  }

  /**
   * Tests Color Scheme admin pages permissions.
   */
  public function testColorSchemes(): void {
    $this->ensureRoleAccountsConfigured();

    // A Site Manager WITHOUT 'administer utexas announcement color schemes'
    // cannot access the color scheme administration pages.
    $this->loginAsPlaywright(self::$siteManagerAccount['name'], self::$siteManagerAccount['password']);
    $this->assertStatusCode('/admin/config/site-announcement/color-scheme', 403);
    $this->assertStatusCode('/admin/config/site-announcement/color-scheme/add', 403);
    $this->assertStatusCode('/admin/config/site-announcement/color-scheme/yellow_black/edit', 403);
    $this->assertStatusCode('/admin/config/site-announcement/color-scheme/yellow_black/delete', 403);

    // Grant the permission, then confirm access.
    $this->loginAsAdmin();
    $this->grantPermissionViaUi('utexas_site_manager', 'administer utexas announcement color schemes');
    $this->loginAsPlaywright(self::$siteManagerAccount['name'], self::$siteManagerAccount['password']);
    $this->assertStatusCode('/admin/config/site-announcement/color-scheme', 200);
    $this->assertStatusCode('/admin/config/site-announcement/color-scheme/add', 200);
    $this->assertStatusCode('/admin/config/site-announcement/color-scheme/yellow_black/edit', 200);
    $this->assertStatusCode('/admin/config/site-announcement/color-scheme/yellow_black/delete', 200);
  }

  /**
   * Tests the Site Announcement admin page permissions.
   */
  public function testSiteAnnouncement(): void {
    $this->ensureRoleAccountsConfigured();

    // A Content Editor WITHOUT 'manage utexas site announcement' cannot
    // access the administration page.
    $this->loginAsPlaywright(self::$contentEditorAccount['name'], self::$contentEditorAccount['password']);
    $this->assertStatusCode('/admin/config/site-announcement', 403);

    // Grant the permission to Site Manager, then confirm access.
    $this->loginAsAdmin();
    $this->grantPermissionViaUi('utexas_site_manager', 'manage utexas site announcement');
    $this->loginAsPlaywright(self::$siteManagerAccount['name'], self::$siteManagerAccount['password']);
    $this->assertStatusCode('/admin/config/site-announcement', 200);
  }

}
