<?php

namespace Drupal\utexas;

use Drupal\user\Entity\Role;

/**
 * Provided pre-defined permissions for utexas.
 */
class Permissions {

  /**
   * Permissions associated with add-on management.
   *
   * @var array
   */
  public static $manager = [
    'access administration pages',
    'access content overview',
    'access contextual links',
    'access files overview',
    'access media overview',
    'access site in maintenance mode',
    'access toolbar',
    'administer blocks',
    'administer breadcrumbs visibility config',
    'administer drupalkit settings',
    'administer menu',
    'administer nodes',
    'administer page display visibility config',
    'administer twitter widget entities',
    'configure any layout',
    'create and edit custom blocks',
    'create article content',
    'create media',
    'create page content',
    'create terms in tags',
    'create url aliases',
    'create utexas_flex_page content',
    'delete all revisions',
    'delete any article content',
    'delete any page content',
    'delete any utexas_flex_page content',
    'delete article revisions',
    'delete media',
    'delete own article content',
    'delete own page content',
    'delete own utexas_flex_page content',
    'delete page revisions',
    'delete terms in tags',
    'delete utexas_flex_page revisions',
    'edit any article content',
    'edit any page content',
    'edit any utexas_flex_page content',
    'edit own article content',
    'edit own page content',
    'edit own utexas_flex_page content',
    'edit terms in tags',
    'revert all revisions',
    'revert article revisions',
    'revert page revisions',
    'revert utexas_flex_page revisions',
    'update media',
    'use advanced search',
    'use text format basic_html',
    'use text format flex_html',
    'use text format restricted_html',
    'view all media revisions',
    'view all revisions',
    'view article revisions',
    'view own unpublished content',
    'view own unpublished media',
    'view page revisions',
    'view the administration theme',
    'view utexas_flex_page revisions',
  ];

  /**
   * Permissions associated with content editing.
   *
   * @var array
   */
  public static $editor = [
    'access administration pages',
    'access content overview',
    'access contextual links',
    'access files overview',
    'access media overview',
    'access site in maintenance mode',
    'access toolbar',
    'administer blocks',
    'administer breadcrumbs visibility config',
    'administer menu',
    'administer nodes',
    'administer page display visibility config',
    'configure any layout',
    'create and edit custom blocks',
    'create article content',
    'create media',
    'create page content',
    'create terms in tags',
    'create url aliases',
    'create utexas_flex_page content',
    'delete all revisions',
    'delete any article content',
    'delete any page content',
    'delete any utexas_flex_page content',
    'delete article revisions',
    'delete media',
    'delete own article content',
    'delete own page content',
    'delete own utexas_flex_page content',
    'delete page revisions',
    'delete terms in tags',
    'delete utexas_flex_page revisions',
    'edit any article content',
    'edit any page content',
    'edit any utexas_flex_page content',
    'edit own article content',
    'edit own page content',
    'edit own utexas_flex_page content',
    'edit terms in tags',
    'revert all revisions',
    'revert article revisions',
    'revert page revisions',
    'revert utexas_flex_page revisions',
    'update media',
    'use advanced search',
    'use text format basic_html',
    'use text format flex_html',
    'use text format restricted_html',
    'view all media revisions',
    'view all revisions',
    'view article revisions',
    'view own unpublished content',
    'view own unpublished media',
    'view page revisions',
    'view the administration theme',
    'view utexas_flex_page revisions',
  ];

  /**
   * Manipulate an array provided by this class for use in an HTML table.
   *
   * @param array $array
   *   A permissions array defined in this class.
   *
   * @return array
   *   A simple two-value array.
   */
  public static function displayPermissions(array $array) {
    $available_permissions = \Drupal::service('user.permissions')->getPermissions();
    $rows = [];
    foreach ($array as $name) {
      if (in_array($name, array_keys($available_permissions))) {
        $rows[] = [$available_permissions[$name]['title'], $name];
      }
    }
    return $rows;
  }

  /**
   * Retrieve a subset of roles in the system.
   *
   * @return array
   *   An array of Drupal role objects.
   */
  public static function getAllowedRoles() {
    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();
    // Do not allow assigning these permissions to `anonymous`/`authenticated`.
    unset($roles['anonymous']);
    unset($roles['authenticated']);
    return $roles;
  }

  /**
   * Save a set of permissions to a given role.
   *
   * @param string $set
   *   The internal set (either 'manager' or 'editor')
   * @param string $role
   *   A valid Drupal role machine name.
   *
   * @return bool
   *   Whether or not the save was successful.
   */
  public static function assignPermissions($set, $role) {
    $available_permissions = \Drupal::service('user.permissions')->getPermissions();
    if (!$role = Role::load($role)) {
      return FALSE;
    }
    foreach (self::$$set as $permission) {
      if (in_array($permission, array_keys($available_permissions))) {
        $role->grantPermission($permission);
      }
    }
    return $role->save();
  }

}
