<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\utexas_readonly\ReadOnlyHelper;

/**
 * Verifies add-on specific Field UI pages are read-only.
 *
 * @group utexas
 */
class ReadOnlyTest extends FunctionalTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   *
   * @see Drupal\Tests\BrowserTestBase
   */
  protected static $modules = [
    'views_ui',
    'field_ui',
  ];

  /**
   * Test Read Only functionality.
   */
  public function testReadOnly() {
    $this->drupalLogin($this->initializeSuperAdminUser());

    $this->verifyLockedFields();
    $this->verifyReadOnlyPages();
  }

  /**
   * Test default configuration.
   */
  public function verifyLockedFields() {
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
  public function verifyReadOnlyPages() {
    $assert = $this->assertSession();

    $read_only_text = 'This component is read-only and should not be modified.';

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
      '/admin/structure/views/view/content',
      '/admin/structure/views/view/content/delete',
      '/admin/config/content/formats',
      '/admin/config/content/formats/manage/basic_html',
      '/admin/config/content/formats/manage/full_html',
      '/admin/config/content/formats/manage/restricted_html',
    ];
    foreach ($twohundred as $path) {
      $this->assertAllowed($path);
      $assert->pageTextNotContains($read_only_text);
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
      $this->assertForbidden($path);
      $this->assertSession()->pageTextContains($read_only_text);
    }

    $read_only_paths = [];

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
      $this->assertAllowed($path);
      $assert->pageTextContains($read_only_text);
    }
  }

}
