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
 * Playwright-PHP equivalent of SocialLinksTest.php.
 *
 * Pure browser testing — see BaseInstallationTest.php's class docblock for
 * the full rationale. This whole test is HTTP-access status codes gated by
 * a single permission, so it has no Drupal-API-only counterpart at all —
 * there's no split Functional test file for this one.
 *
 * The original test grants 'administer social links data config' to a
 * brand-new Content Editor account via
 * UserTestTrait::initializeContentEditor($extra_permissions). Here, that
 * permission is instead granted directly to the shared
 * utexas_content_editor role via the real /admin/people/permissions
 * checkbox matrix (grantPermissionViaUi(), in PlaywrightHelpersTrait), and
 * the same shared account (from ensureRoleAccountsConfigured()) is used
 * for both the "before" (forbidden) and "after" (allowed) checks.
 *
 * The original test's setUp() also calls copySocialLinksIconFiles(), which
 * copies icon asset files into public:// — a filesystem operation with no
 * bearing on whether these admin routes return 403/200, so it's not
 * needed for the checks ported here. If a future check here needs an
 * actual social link entity (e.g. editing "facebook") to exist, that
 * assumption would need the same scrutiny given to "beacon" in
 * SiteAnnouncementTest.php — not needed for what's ported below, since
 * the original test's own delete-page checks were already commented out.
 */
final class SocialLinksTest extends PlaywrightTestCase {

  use PlaywrightHelpersTrait;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    self::initBaseUrl();
  }

  /**
   * Tests social link icon admin pages permissions.
   */
  public function testSocialLinkIcons(): void {
    $this->ensureRoleAccountsConfigured();

    // A Content Editor WITHOUT 'administer social links data config'
    // cannot access the administration pages.
    $this->loginAsPlaywright(self::$contentEditorAccount['name'], self::$contentEditorAccount['password']);
    $this->assertStatusCode('/admin/structure/social-links', 403);
    $this->assertStatusCode('/admin/structure/social-links/add', 403);
    $this->assertStatusCode('/admin/structure/social-links/facebook/edit', 403);

    // Grant the permission, then confirm access.
    $this->loginAsAdmin();
    $this->grantPermissionViaUi('utexas_content_editor', 'administer social links data config');
    $this->loginAsPlaywright(self::$contentEditorAccount['name'], self::$contentEditorAccount['password']);
    $this->assertStatusCode('/admin/structure/social-links', 200);
    $this->assertStatusCode('/admin/structure/social-links/add', 200);
    $this->assertStatusCode('/admin/structure/social-links/facebook/edit', 200);
  }

}
