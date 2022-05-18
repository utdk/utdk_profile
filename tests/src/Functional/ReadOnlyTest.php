<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\utexas_readonly\ReadOnlyHelper;

/**
 * Verifies add-on specific Field UI pages are read-only.
 *
 * @group utexas
 */
class ReadOnlyTest extends BrowserTestBase {

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
  protected $defaultTheme = 'forty_acres';

  /**
   * Modules to enable.
   *
   * @var array
   *
   * @see Drupal\Tests\BrowserTestBase
   */
  protected static $modules = [
    'views_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->strictConfigSchema = NULL;
    parent::setUp();
    $available_permissions = \Drupal::service('user.permissions')->getPermissions();
    $admin_user = $this->drupalCreateUser(array_keys($available_permissions));
    $this->drupalLogin($admin_user);
  }

  /**
   * Test default configuration.
   */
  public function testLockedFields() {
    $locked_field_storage = [
      'block_content.field_utexas_sl_social_links',
      'block_content.field_utexas_call_to_action_link',
      'node.field_flex_page_metatags',
      'block_content.field_block_featured_highlight',
      'block_content.field_block_fca',
      'block_content.field_block_hero',
      'block_content.field_utexas_flex_list_items',
      'block_content.field_block_il',
      'media.field_media_file',
      'media.field_media_oembed_video',
      'media.field_utexas_media_image',
      'block_content.field_block_pca',
      'block_content.field_block_pl',
      'block_content.field_block_pu',
      'block_content.field_block_ql',
      'block_content.field_block_resources',
    ];
    foreach ($locked_field_storage as $field_storage) {
      $config = $this->config('field.storage.' . $field_storage);
      // Verify all fields are unlocked in configuration.
      $this->assertEquals($config->get('locked'), FALSE);
    }

  }

  /**
   * Test which pages admin users have access to.
   */
  public function testReadOnlyPages() {
    // Pages that an admin user *should* have access to.
    $twohundred = [
      '/admin/structure/types/manage/page/fields',
      '/admin/structure/types/manage/page/form-display',
      '/admin/structure/types/manage/page/display',
      '/admin/structure/types/manage/page/fields/add-field',
      '/admin/structure/block/block-content/manage/basic/fields',
      '/admin/structure/block/block-content/manage/basic/fields/add-field',
      '/admin/structure/block/block-content/manage/basic/form-display',
      '/admin/structure/block/block-content/manage/basic/display',
      '/admin/structure/block/block-content/manage/basic/fields/block_content.basic.body/storage',
      '/admin/structure/views/view/content',
      '/admin/structure/views/view/content/delete',
      '/admin/config/content/formats',
      '/admin/config/content/formats/manage/basic_html',
      '/admin/config/content/formats/manage/full_html',
      '/admin/config/content/formats/manage/restricted_html',
    ];
    foreach ($twohundred as $path) {
      $this->isAccessible($path);
    }
    // Pages that should be forbidden (403).
    $fourohthree = [];
    foreach (ReadOnlyHelper::$restrictedNodeTypes as $machine_name) {
      $fourohthree[] = '/admin/structure/types/manage/' . $machine_name . '/fields/add-field';
    }
    foreach (ReadOnlyHelper::$restrictedBlockTypes as $machine_name) {
      $fourohthree[] = '/admin/structure/block/block-content/manage/' . $machine_name . '/fields/add-field';
    }
    foreach ($fourohthree as $path) {
      $this->isNotAccessible($path);
    }

    $read_only_paths = [];
    // Check restricted field storage.
    $read_only_paths[] = '/admin/structure/block/block-content/manage/call_to_action/fields/block_content.call_to_action.field_utexas_call_to_action_link/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/utexas_featured_highlight/fields/block_content.utexas_featured_highlight.field_block_featured_highlight/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/utexas_flex_list/fields/block_content.utexas_flex_list.field_utexas_flex_list_items/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/utexas_hero/fields/block_content.utexas_hero.field_block_hero/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/utexas_image_link/fields/block_content.utexas_image_link.field_block_il/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/utexas_photo_content_area/fields/block_content.utexas_photo_content_area.field_block_pca/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/utexas_promo_list/fields/block_content.utexas_promo_list.field_block_pl/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/utexas_promo_unit/fields/block_content.utexas_promo_unit.field_block_pu/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/utexas_quick_links/fields/block_content.utexas_quick_links.field_block_ql/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/utexas_resources/fields/block_content.utexas_resources.field_block_resources/storage';
    $read_only_paths[] = '/admin/structure/block/block-content/manage/social_links/fields/block_content.social_links.field_utexas_sl_social_links/storage';

    foreach (ReadOnlyHelper::$restrictedNodeTypes as $machine_name) {
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/fields';
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/form-display';
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/display';
    }
    foreach (ReadOnlyHelper::$restrictedBlockTypes as $machine_name) {
      $read_only_paths[] = '/admin/structure/block/block-content/manage/' . $machine_name . '/fields';
    }
    foreach (ReadOnlyHelper::$restrictedMediaTypes as $machine_name) {
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/fields';
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/form-display';
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/display';
    }
    // Pages that should be read-only.
    foreach ($read_only_paths as $path) {
      $this->isReadOnly($path);
    }
  }

  /**
   * Check that a given path can be accessed.
   *
   * @param string $path
   *   A Drupal admin URL.
   */
  private function isAccessible($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('This component is read-only and should not be modified.');
  }

  /**
   * Check that a given path can be accessed.
   *
   * @param string $path
   *   A Drupal admin URL.
   */
  private function isNotAccessible($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->pageTextContains('This component is read-only and should not be modified.');
  }

  /**
   * Check that a given path can be accessed but is read-only.
   *
   * @param string $path
   *   A Drupal admin URL.
   */
  private function isReadOnly($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('This component is read-only and should not be modified.');
  }

}