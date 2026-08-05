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
use function Playwright\Testing\expect;

/**
 * Playwright-PHP equivalent of BaseInstallationTest.php's browser-only checks.
 *
 * Pure browser testing — no Drupal PHP API, no kernel bootstrap, no Mink,
 * no Selenium. Every setup step (creating nodes, creating role accounts,
 * installing a module) is done the same way a real user would: through the
 * running site's actual admin UI, driven by Playwright. That's the
 * dividing line drawn for this suite — Playwright handles browser
 * testing, and Drupal's own testing API
 * (tests/src/Functional/BaseInstallationTest.php) handles everything that
 * is fundamentally a Drupal-API concern rather than app behavior:
 * - Config assertions (module status, CKEditor toolbar, filter statuses,
 *   text format list, language, metatag defaults) — these read
 *   \Drupal::config() directly; there's no browser page that renders them
 *   as a single fact to assert on.
 * - FilterFormat::access() and filter_formats() ordering — permission/
 *   config-system checks, not page content.
 *
 * The Site Manager/Content Editor permission sets that
 * UserTestTrait::initializeSiteManager() grants programmatically via
 * Permissions::assignPermissions() DO have a real UI equivalent after all
 * — the utexas_permissions_config form at
 * /admin/config/content/utexas/permissions (see
 * Drupal\utexas\Form\PermissionsConfigurationForm), which calls that exact
 * same method from a "Assign management permissions" / "Assign content
 * editing permissions" role select. ensureRoleAccountsConfigured()
 * (PlaywrightHelpersTrait) drives that form directly, once per test run.
 *
 * Requires:
 * - `composer require --dev playwright-php/playwright` (already a root
 *   composer.json dependency) and Node.js + browsers installed wherever
 *   this test runs (e.g. inside the Lando appserver, via
 *   `vendor/bin/playwright-install`).
 * - ADMIN_USER / ADMIN_PASSWORD env vars for an account with permission to
 *   create content, create users, and install modules on the target site.
 * - BASE_URL env var (defaults to https://utdk-project.lndo.site).
 *
 * Every Playwright call below is verified directly against the installed
 * vendor/playwright-php/playwright source (not just its README/guide docs,
 * which undersell the actual API — see toContainText(), check(),
 * selectOption(), and Response::status(), none of which are documented
 * there but all of which exist and are used here).
 *
 * Note: toHaveAttribute() only does an exact-value match (confirmed in
 * Playwright\Testing\Expect::toHaveAttribute()), so partial matches (e.g.
 * an image style path that's part of a longer generated src URL) use
 * getAttribute() + assertStringContainsString() instead.
 *
 * Admin form field selectors below (title[0][value], pass[pass1]/pass2,
 * roles[...]) are confirmed against core's AccountForm/PasswordConfirm
 * element source, but the exact rendered form has not been run against a
 * live site as part of writing this file — verify before trusting it.
 */
final class BaseInstallationTest extends PlaywrightTestCase {

  use PlaywrightHelpersTrait;

  private const MENU_LINK_ITEM_ID = 9;

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    self::initBaseUrl();
  }

  /**
   * Tests that a page node renders the Social Sharing block.
   */
  public function testPageNodeRendersSocialSharingBlock(): void {
    $this->loginAsAdmin();
    $nid = $this->createNodeViaUi('page', 'Test Page');

    $this->page->goto(self::$baseUrl . '/node/' . $nid);
    expect($this->page->locator('body'))->toContainText('Share this content');
  }

  /**
   * Tests that a flex page node renders the expected meta tags.
   */
  public function testFlexPageNodeRendersExpectedMetaTags(): void {
    $this->loginAsAdmin();
    $nid = $this->createNodeViaUi('utexas_flex_page', 'Test Page', [
      'field_flex_page_summary[0][value]' => 'Test summary text.',
    ]);

    $this->page->goto(self::$baseUrl . '/node/' . $nid);
    expect($this->page->locator('meta[name="description"]'))
      ->toHaveAttribute('content', 'Test summary text.');
    expect($this->page->locator('meta[name="twitter:description"]'))
      ->toHaveAttribute('content', 'Test summary text.');
    expect($this->page->locator('meta[property="og:description"]'))
      ->toHaveAttribute('content', 'Test summary text.');
  }

  /**
   * Tests that setting the main menu link's target renders correctly.
   */
  public function testMainMenuLinkTargetBehavior(): void {
    $this->ensureRoleAccountsConfigured();
    $this->loginAsPlaywright(self::$contentEditorAccount['name'], self::$contentEditorAccount['password']);

    $this->page->goto(self::$baseUrl . '/admin/structure/menu/item/' . self::MENU_LINK_ITEM_ID . '/edit');
    $this->page->locator('input[name="target[_blank]"]')->check();
    $this->page->locator('input[name="link[0][uri]"]')->fill('https://utexas.edu');
    $this->page->locator('select[name="class"]')->selectOption('ut-cta-link--lock');
    $this->page->locator('text=Save')->click();

    $this->page->goto(self::$baseUrl . '/');
    $l1Link = $this->page->locator('.l1-link')->first();
    expect($l1Link)->toHaveAttribute('target', '_blank');
    expect($l1Link)->toHaveClass('ut-cta-link--lock');

    // Set the menu item to a non-link and change the link options.
    $this->page->goto(self::$baseUrl . '/admin/structure/menu/item/' . self::MENU_LINK_ITEM_ID . '/edit');
    $this->page->locator('input[name="target[_blank]"]')->uncheck();
    $this->page->locator('input[name="link[0][uri]"]')->fill('<nolink>');
    $this->page->locator('select[name="class"]')->selectOption('ut-cta-link--external');
    $this->page->locator('text=Save')->click();

    $this->page->goto(self::$baseUrl . '/');
    $l1Link = $this->page->locator('.l1-link')->first();
    expect($l1Link)->not()->toHaveAttribute('target', '_blank');
    expect($l1Link)->toHaveClass('ut-cta-link--external');

    // Confirm link options can be completely unset.
    $this->page->goto(self::$baseUrl . '/admin/structure/menu/item/' . self::MENU_LINK_ITEM_ID . '/edit');
    $this->page->locator('select[name="class"]')->selectOption('0');
    $this->page->locator('text=Save')->click();

    $this->page->goto(self::$baseUrl . '/');
    $l1Link = $this->page->locator('.l1-link')->first();
    expect($l1Link)->not()->toHaveClass('ut-cta-link--external');
  }

  /**
   * Tests Content Editor role access to various admin routes.
   */
  public function testContentEditorRoleAccess(): void {
    $this->ensureRoleAccountsConfigured();
    $this->loginAsPlaywright(self::$contentEditorAccount['name'], self::$contentEditorAccount['password']);

    // Make sure a Content Editor doesn't have access to Field UI.
    $this->assertStatusCode('/admin/structure/types/manage/utexas_flex_page/fields', 404);
    // Make sure a Content Editor doesn't have access to Block UI.
    $this->assertStatusCode('/admin/structure/block', 403);
    // Make sure a Content Editor has access to Block Content tab.
    $this->assertStatusCode('/admin/content/block', 200);
    // Make sure a Content Editor has access to create Block Content.
    $this->assertStatusCode('/block/add', 200);
  }

  /**
   * Tests Site Manager role access to various admin routes.
   */
  public function testSiteManagerRoleAccess(): void {
    $this->ensureRoleAccountsConfigured();
    $this->loginAsPlaywright(self::$siteManagerAccount['name'], self::$siteManagerAccount['password']);

    // Make sure a Site Manager doesn't have access to the Field UI.
    $this->assertStatusCode('/admin/structure/types/manage/utexas_flex_page/fields', 404);
    // Make sure a Site Manager has access to the Block UI.
    $this->assertStatusCode('/admin/structure/block', 200);
    // Make sure a Site Manager has access to the Block Content tab.
    $this->assertStatusCode('/admin/content/block', 200);
    // Make sure a Site Manager doesn't have access to the permissions page.
    $this->assertStatusCode('/admin/people/permissions', 403);
    // Site managers cannot access the Layout Builder Styles config page.
    $this->assertStatusCode('/admin/config/content/layout_builder_style', 403);
  }

  /**
   * Tests that demo content renders as expected after enabling utexas_devel.
   */
  public function testDemoContentRendersAsExpected(): void {
    $this->loginAsAdmin();
    $this->installModuleViaUi('utexas_devel');

    $this->page->goto(self::$baseUrl . '/featured-highlight');
    $this->assertStringContainsString(
      'styles/utexas_image_style_600w/public/generated_sample/tower-lighting.gif',
      (string) $this->page->locator('.utexas-featured-highlight .image-wrapper img')->getAttribute('src'),
    );
    expect($this->page->locator('h2.ut-headline a'))->toContainText('Featured Highlight');
    expect($this->page->locator('.utexas-featured-highlight .ut-copy'))
      ->toContainText('Add descriptive text to provide a short summary of this featured content.');
    expect($this->page->locator('.utexas-featured-highlight a.ut-btn'))->toContainText('Visit UTexas');
    expect($this->page->locator('body'))->toContainText('June 12, 2019');

    $this->page->goto(self::$baseUrl . '/flex-content-area');
    $this->assertStringContainsString(
      'styles/utexas_image_style_340w_227h/public/generated_sample/tower-lighting.gif',
      (string) $this->page->locator('.ut-flex-content-area .image-wrapper img')->getAttribute('src'),
    );
    expect($this->page->locator('.ut-flex-content-area h3.ut-headline a'))->toContainText('Flex Content Area 1');
    expect($this->page->locator('.ut-flex-content-area .ut-copy'))
      ->toContainText('The Flex Content Area has a number of display options.');
    expect($this->page->locator('.ut-flex-content-area a.ut-btn'))->toContainText('Visit UTexas');

    $this->page->goto(self::$baseUrl . '/promo-list');
    $this->assertStringContainsString(
      'styles/utexas_image_style_64w_64h/public/generated_sample/tower-lighting.gif',
      (string) $this->page->locator('.promo-list .image-wrapper img')->getAttribute('src'),
    );
    expect($this->page->locator('.utexas-promo-list-container h3.ut-headline--underline'))
      ->toContainText('Promo List Group 1');
    expect($this->page->locator('.promo-list .content'))->toContainText('Short descriptive text can be formatted.');

    $this->page->goto(self::$baseUrl . '/promo-unit');
    $this->assertStringContainsString(
      'styles/utexas_image_style_800w_500h/public/generated_sample/tower-lighting.gif',
      (string) $this->page->locator('.utexas-promo-unit .image-wrapper img')->getAttribute('src'),
    );
    expect($this->page->locator('.utexas-promo-unit-container h3.ut-headline--underline'))
      ->toContainText('Promo Unit Group 1');
    expect($this->page->locator('.utexas-promo-unit .data-wrapper p'))
      ->toContainText('Short descriptive text can be formatted.');

    $this->page->goto(self::$baseUrl . '/photo-content-area');
    $this->assertStringContainsString(
      'styles/utexas_image_style_450w_600h/public/generated_sample/tower-lighting.gif',
      (string) $this->page->locator('.ut-photo-content-area .photo-wrapper img')->getAttribute('src'),
    );
    expect($this->page->locator('.ut-photo-content-area h2.ut-headline'))->toContainText('Photo Content Area');

    $this->page->goto(self::$baseUrl . '/hero-default');
    $this->assertStringContainsString(
      'styles/utexas_image_style_720w_389h/public/generated_sample/tower-lighting.gif',
      (string) $this->page->locator('.ut-hero img')->getAttribute('src'),
    );
    expect($this->page->locator('.hero--caption-credit-wrapper .credit'))
      ->toContainText('Copyright University of Texas at Austin');
    expect($this->page->locator('.hero--caption-credit-wrapper .hero-caption'))
      ->toContainText('A short caption may be added, describing the hero');

    $this->page->goto(self::$baseUrl . '/quick-links');
    expect($this->page->locator('.utexas-quick-links h3.ut-headline'))->toContainText('Quick Links');
    expect($this->page->locator('.utexas-quick-links .ut-copy'))
      ->toContainText('Quick links include a headline, copy text, and links.');
    expect($this->page->locator('.utexas-quick-links .link-list a'))->toContainText('Our commitment to diversity');

    $this->page->goto(self::$baseUrl . '/resources');
    $this->assertStringContainsString(
      'styles/utexas_image_style_400w_250h/public/generated_sample/tower-lighting.gif',
      (string) $this->page->locator('.utexas-resource .image-wrapper img')->getAttribute('src'),
    );
    expect($this->page->locator('.ut-resources-wrapper h3.ut-headline--underline'))->toContainText('Resource Group 1');
    expect($this->page->locator('.utexas-resource-items .utexas-resource h3.ut-headline'))->toContainText('Resource 1');
  }

}
