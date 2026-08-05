<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies field storage locked-status configuration.
 *
 * VerifyReadOnlyPages() (HTTP-access status codes + read-only notice text
 * across dozens of admin routes) is covered by the Playwright equivalent's
 * testReadOnlyPages() (tests/playwright/ReadOnlyTest.php) — not duplicated
 * here. What remains is a pure \Drupal::config() read with no browser
 * involved.
 */
#[RunTestsInSeparateProcesses]
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
    $this->verifyLockedFields();
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

}
