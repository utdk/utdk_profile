<?php

namespace Drupal\Tests\utexas\Traits;

use Drupal\utexas\Permissions;

/**
 * General-purpose methods for interacting with Drupal users.
 */
trait UserTestTrait {

  /**
   * Asserts that a user role has a set of permissions.
   *
   * @param \Drupal\user\RoleInterface|string $role
   *   The user role, or its ID.
   * @param string|string[] $permissions
   *   The permission(s) to check.
   */
  protected function assertPermissions($role, $permissions) {
    if (is_string($role)) {
      $role = Role::load($role);
    }
    $this->assertContainsAll((array) $permissions, $role->getPermissions());
  }

  /**
   * Asserts that the current user can access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  protected function assertAllowed($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Asserts that the current user cannot access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  protected function assertForbidden($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Create a user with Content Editor specific role.
   */
  protected function initializeContentEditor() {
    $this->testUser = $this->drupalCreateUser();
    $testUser = user_load_by_name($this->testUser->getAccountName());
    $testUser->addRole('utexas_content_editor');
    $testUser->save();
    $this->drupalLogin($this->testUser);
  }

  /**
   * Create a user with Site Manager specific role.
   */
  protected function initializeSiteManager() {
    // The 'utexas_site_manager' role is not enabled by default on generic
    // UTDK3 sites, so we enable it for testing purposes.
    \Drupal::service('module_installer')->install(['utexas_role_site_manager']);
    Permissions::assignPermissions('manager', 'utexas_site_manager');
    Permissions::assignPermissions('editor', 'utexas_site_manager');
    $this->testUser = $this->drupalCreateUser();
    $testUser = user_load_by_name($this->testUser->getAccountName());
    $testUser->addRole('utexas_site_manager');
    $testUser->save();
    $this->drupalLogin($this->testUser);
  }

  /**
   * Create a generic admin user with common permissions.
   *
   * @param array $extra_permissions
   *   Optionally provide extra permissions for the user.
   *
   * @return \Drupal\user\Entity\User
   *   The user object for usage in the test.
   */
  protected function initializeAdminUser(array $extra_permissions = []) {
    $standard_permissions = [
      'administer site configuration',
      'use text format restricted_html',
      'view the administration theme',
    ];
    if (!empty($extra_permissions)) {
      $standard_permissions = array_merge($standard_permissions, $extra_permissions);
    }
    $user = $this->drupalCreateUser($standard_permissions);
    return $user;
  }

}
