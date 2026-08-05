<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Playwright;

/**
 * Shared browser-only helpers for this profile's Playwright-PHP tests.
 *
 * No Drupal PHP API, no kernel bootstrap, no Mink, no Selenium — every
 * helper here drives the real running site's admin UI via Playwright,
 * exactly the way a real user would. See BaseInstallationTest.php's class
 * docblock for the full rationale and history behind this design.
 *
 * Static properties declared here are per-consuming-class in PHP (each
 * class using this trait gets its own copy), so each test class that uses
 * ensureRoleAccountsConfigured() creates and reuses its own pair of
 * Site Manager/Content Editor accounts — there's no cross-class sharing,
 * matching how each Functional test class previously created its own
 * fresh users via FunctionalTestBase::setUp().
 */
trait PlaywrightHelpersTrait {

  /**
   * Base URL of the running site under test.
   */
  private static string $baseUrl;

  /**
   * Whether ensureRoleAccountsConfigured() has already run this test run.
   */
  private static bool $roleAccountsConfigured = FALSE;

  /**
   * The shared Site Manager account: ['name' => ..., 'password' => ...].
   */
  private static array $siteManagerAccount;

  /**
   * The shared Content Editor account: ['name' => ..., 'password' => ...].
   */
  private static array $contentEditorAccount;

  /**
   * Reads BASE_URL from the environment; call from setUpBeforeClass().
   */
  private static function initBaseUrl(): void {
    self::$baseUrl = rtrim(getenv('BASE_URL') ?: 'https://utdk-project.lndo.site', '/');
  }

  /**
   * Logs the Playwright browser in as the given user via the UI form.
   */
  private function loginAsPlaywright(string $name, string $password): void {
    $this->page->goto(self::$baseUrl . '/user/login');
    $this->page->locator('input[name="name"]')->fill($name);
    $this->page->locator('input[name="pass"]')->fill($password);
    $this->page->locator('text=Log in')->click();
  }

  /**
   * Logs the Playwright browser in as the admin account from env vars.
   */
  private function loginAsAdmin(): void {
    $user = getenv('ADMIN_USER');
    $password = getenv('ADMIN_PASSWORD');
    if (!$user || !$password) {
      throw new \RuntimeException('ADMIN_USER and ADMIN_PASSWORD env vars are required to run this test.');
    }
    $this->loginAsPlaywright($user, $password);
  }

  /**
   * Creates a node via the real /node/add/{type} admin form.
   *
   * @param string $type
   *   The content type's machine name.
   * @param string $title
   *   The node title.
   * @param array<string, string> $fieldValues
   *   Map of form field name attribute => value, beyond the title.
   *
   * @return int
   *   The created node's ID, parsed from the post-save redirect URL.
   */
  private function createNodeViaUi(string $type, string $title, array $fieldValues = []): int {
    $this->page->goto(self::$baseUrl . '/node/add/' . $type);
    $this->page->locator('input[name="title[0][value]"]')->fill($title);
    foreach ($fieldValues as $name => $value) {
      $this->page->locator('[name="' . $name . '"]')->fill($value);
    }
    $this->page->locator('text=Save')->click();

    if (!preg_match('#/node/(\d+)#', $this->page->url(), $matches)) {
      throw new \RuntimeException('Could not determine node ID after save; unexpected URL: ' . $this->page->url());
    }
    return (int) $matches[1];
  }

  /**
   * Creates a user with the given role via the real /admin/people/create form.
   *
   * @return array{name: string, password: string}
   *   The created user's username and plaintext password.
   */
  private function createUserViaUi(string $role): array {
    $name = 'pw_test_' . $role . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
    $password = 'Pw!' . bin2hex(random_bytes(6));

    $this->page->goto(self::$baseUrl . '/admin/people/create');
    $this->page->locator('input[name="name"]')->fill($name);
    $this->page->locator('input[name="mail"]')->fill($name . '@example.com');
    $this->page->locator('input[name="pass[pass1]"]')->fill($password);
    $this->page->locator('input[name="pass[pass2]"]')->fill($password);
    // 'status' is a radio group (Blocked=0, Active=1), not a checkbox — the
    // "Active" option must be targeted by value, since both radios share
    // the same name attribute.
    $this->page->locator('input[name="status"][value="1"]')->check();
    $this->page->locator('input[name="roles[' . $role . ']"]')->check();
    $this->page->locator('text=Create new account')->click();

    return ['name' => $name, 'password' => $password];
  }

  /**
   * Installs a module via the real /admin/modules checkbox + Install button.
   */
  private function installModuleViaUi(string $module): void {
    $this->page->goto(self::$baseUrl . '/admin/modules');
    $this->page->locator('input[name="modules[' . $module . '][enable]"]')->check();
    $this->page->locator('text=Install')->click();
  }

  /**
   * Grants the manager/editor permission sets to roles via the real form.
   *
   * /admin/config/content/utexas/permissions —
   * Drupal\utexas\Form\PermissionsConfigurationForm.
   */
  private function assignRolePermissionsViaUi(string $managerRole, string $editorRole): void {
    $this->page->goto(self::$baseUrl . '/admin/config/content/utexas/permissions');
    $this->page->locator('select[name="assign_manager_permissions"]')->selectOption($managerRole);
    $this->page->locator('select[name="assign_editor_permissions"]')->selectOption($editorRole);
    $this->page->locator('text=Add permissions to selected role(s)')->click();
  }

  /**
   * Grants a single permission to a role via the real permissions matrix.
   *
   * /admin/people/permissions — Drupal\user\Form\UserPermissionsForm. Each
   * checkbox's #parents is [$rid, $perm] (confirmed directly in that
   * form's source), so the checkbox's name attribute is "{role}[{perm}]" —
   * note permission machine names contain spaces (e.g. "administer utexas
   * announcement icons"), which is valid inside a quoted CSS attribute
   * selector.
   */
  private function grantPermissionViaUi(string $role, string $permission): void {
    $this->page->goto(self::$baseUrl . '/admin/people/permissions');
    $this->page->locator('input[name="' . $role . '[' . $permission . ']"]')->check();
    $this->page->locator('text=Save permissions')->click();
  }

  /**
   * Creates the shared Site Manager/Content Editor accounts on first use.
   *
   * Installs the utexas_role_site_manager module (the role doesn't exist
   * without it — see utexas_role_site_manager/config/install/
   * user.role.utexas_site_manager.yml, which ships with an empty
   * permissions set), creates one account per role via the real admin UI,
   * then grants each role its permission set via the
   * utexas_permissions_config form. Runs once per test run; every
   * subsequent call reuses the same two accounts.
   */
  private function ensureRoleAccountsConfigured(): void {
    if (self::$roleAccountsConfigured) {
      return;
    }

    $this->loginAsAdmin();
    $this->installModuleViaUi('utexas_role_site_manager');

    self::$siteManagerAccount = $this->createUserViaUi('utexas_site_manager');
    self::$contentEditorAccount = $this->createUserViaUi('utexas_content_editor');

    $this->assignRolePermissionsViaUi('utexas_site_manager', 'utexas_content_editor');

    self::$roleAccountsConfigured = TRUE;
  }

  /**
   * Navigates to a path and asserts the response's HTTP status code.
   */
  private function assertStatusCode(string $path, int $expected): void {
    $response = $this->page->goto(self::$baseUrl . $path);
    $this->assertNotNull($response, "No response received for $path");
    $this->assertSame($expected, $response->status(), "Unexpected status code for $path");
  }

}
