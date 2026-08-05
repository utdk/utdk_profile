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
 * Playwright-PHP equivalent of ReadOnlyTest.php's browser-only checks.
 *
 * Pure browser testing — see BaseInstallationTest.php's class docblock for
 * the full rationale. verifyLockedFields() (a pure \Drupal::config() read)
 * stays in the Drupal-native tests/src/Functional/ReadOnlyTest.php; this
 * file covers verifyReadOnlyPages() only.
 *
 * The original test logs in as a "super admin" via
 * UserTestTrait::initializeSuperAdminUser(), which grants every permission
 * in the system — there's no practical UI equivalent to checking every
 * permission checkbox for a brand-new role. loginAsAdmin() (from
 * PlaywrightHelpersTrait) is used instead, on the assumption ADMIN_USER is
 * the site's actual super admin account (uid 1, or otherwise granted every
 * permission this test exercises) — the same real-world account that
 * would be used to verify this in production.
 *
 * ReadOnlyHelper's static arrays ($restrictedNodeTypes,
 * $restrictedBlockTypes, $restrictedMediaTypes) are hardcoded below rather
 * than referenced from Drupal\utexas_readonly\ReadOnlyHelper, since this
 * file has no Drupal PHP API access at all. If that module's lists change,
 * these need updating by hand — noted here so that's not a silent gap.
 */
final class ReadOnlyTest extends PlaywrightTestCase {

  use PlaywrightHelpersTrait;

  private const READ_ONLY_TEXT = 'This component is read-only and should not be modified.';

  /**
   * Mirrors Drupal\utexas_readonly\ReadOnlyHelper::$restrictedNodeTypes.
   */
  private const RESTRICTED_NODE_TYPES = ['utexas_flex_page'];

  /**
   * Mirrors Drupal\utexas_readonly\ReadOnlyHelper::$restrictedBlockTypes.
   */
  private const RESTRICTED_BLOCK_TYPES = [
    'call_to_action',
    'utexas_featured_highlight',
    'utexas_flex_content_area',
    'utexas_flex_list',
    'utexas_hero',
    'utexas_image_link',
    'utexas_instagram',
    'utexas_photo_content_area',
    'utexas_promo_list',
    'utexas_promo_unit',
    'utexas_quick_links',
    'utexas_resources',
    'social_links',
  ];

  /**
   * Mirrors Drupal\utexas_readonly\ReadOnlyHelper::$restrictedMediaTypes.
   */
  private const RESTRICTED_MEDIA_TYPES = [
    'utexas_image',
    'utexas_video_external',
    'utexas_document',
  ];

  /**
   * Image styles provided by the kernel — copied from the original test.
   */
  private const UTEXAS_IMAGE_STYLES = [
    'utexas_image_style_1000w', 'utexas_image_style_1000w_600h',
    'utexas_image_style_1000w_666h', 'utexas_image_style_112w_112h',
    'utexas_image_style_1140w_616h', 'utexas_image_style_1200w',
    'utexas_image_style_1200w_750h', 'utexas_image_style_120w_150h',
    'utexas_image_style_128w_128h', 'utexas_image_style_1350w',
    'utexas_image_style_140w_140h', 'utexas_image_style_1440w_778h',
    'utexas_image_style_150w_188h', 'utexas_image_style_1600w',
    'utexas_image_style_1600w_500h', 'utexas_image_style_170w_170h',
    'utexas_image_style_176w_112h', 'utexas_image_style_1800w',
    'utexas_image_style_1800w_2400h', 'utexas_image_style_1920w_1038h',
    'utexas_image_style_2000w', 'utexas_image_style_220w_140h',
    'utexas_image_style_2250w_900h', 'utexas_image_style_2280w_1232h',
    'utexas_image_style_250w_150h', 'utexas_image_style_280w_152h',
    'utexas_image_style_280w_280h', 'utexas_image_style_300w_376h',
    'utexas_image_style_3200w', 'utexas_image_style_3200w_1000h',
    'utexas_image_style_330w_200h', 'utexas_image_style_340w_227h',
    'utexas_image_style_400w_250h', 'utexas_image_style_440w_280h',
    'utexas_image_style_450w_300h', 'utexas_image_style_450w_600h',
    'utexas_image_style_500w', 'utexas_image_style_500w_300h',
    'utexas_image_style_500w_333h', 'utexas_image_style_500w_500h',
    'utexas_image_style_600w', 'utexas_image_style_600w_375h',
    'utexas_image_style_64w_64h', 'utexas_image_style_660w_400h',
    'utexas_image_style_675w', 'utexas_image_style_680w_454h',
    'utexas_image_style_720w_389h', 'utexas_image_style_800w_500h',
    'utexas_image_style_85w_85h', 'utexas_image_style_900w',
    'utexas_image_style_900w_1200h', 'utexas_image_style_900w_600h',
    'utexas_image_style_960w_519h',
  ];

  /**
   * {@inheritdoc}
   */
  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    self::initBaseUrl();
  }

  /**
   * Asserts a 200 response whose body does not contain the read-only notice.
   */
  private function assertAllowedWithoutReadOnlyNotice(string $path): void {
    $this->assertStatusCode($path, 200);
    expect($this->page->locator('body'))->not()->toContainText(self::READ_ONLY_TEXT);
  }

  /**
   * Asserts a 403 response whose body contains the read-only notice.
   */
  private function assertForbiddenWithReadOnlyNotice(string $path): void {
    $this->assertStatusCode($path, 403);
    expect($this->page->locator('body'))->toContainText(self::READ_ONLY_TEXT);
  }

  /**
   * Asserts a 200 response whose body contains the read-only notice.
   */
  private function assertAllowedWithReadOnlyNotice(string $path): void {
    $this->assertStatusCode($path, 200);
    expect($this->page->locator('body'))->toContainText(self::READ_ONLY_TEXT);
  }

  /**
   * Tests which Field UI/display/image-style pages are read-only.
   */
  public function testReadOnlyPages(): void {
    $this->loginAsAdmin();

    // Pages that an admin user *should* have access to (no read-only notice).
    $twohundred = [
      '/admin/structure/types/manage/page/fields',
      '/admin/structure/types/manage/page/form-display',
      '/admin/structure/types/manage/page/display',
      '/admin/structure/types/manage/page/fields/add-field',
      '/admin/structure/block-content/manage/basic/fields',
      '/admin/structure/block-content/manage/basic/fields/add-field',
      '/admin/structure/block-content/manage/basic/form-display',
      '/admin/structure/block-content/manage/basic/display',
      '/admin/structure/views/view/content',
      '/admin/structure/views/view/content/delete',
      '/admin/config/content/formats',
      '/admin/config/content/formats/manage/basic_html',
      '/admin/config/content/formats/manage/full_html',
      '/admin/config/content/formats/manage/restricted_html',
      '/admin/config/media/image-styles/manage/media_library',
      '/admin/config/media/image-styles/manage/medium',
      '/admin/config/media/image-styles/manage/medium/delete',
      '/admin/config/media/image-styles/manage/thumbnail',
      '/admin/config/media/image-styles/manage/thumbnail/delete',
      '/admin/config/media/image-styles/manage/large',
      '/admin/config/media/image-styles/manage/large/delete',
    ];
    // Users *should* be able to flush image styles provided by the kernel.
    foreach (self::UTEXAS_IMAGE_STYLES as $style) {
      $twohundred[] = '/admin/config/media/image-styles/manage/' . $style . '/flush';
    }
    foreach ($twohundred as $path) {
      $this->assertAllowedWithoutReadOnlyNotice($path);
    }

    // Pages that should be forbidden (403), with the read-only notice.
    $fourohthree = [];
    foreach (self::RESTRICTED_NODE_TYPES as $machine_name) {
      $fourohthree[] = '/admin/structure/types/manage/' . $machine_name . '/fields/add-field';
    }
    foreach (self::RESTRICTED_BLOCK_TYPES as $machine_name) {
      $fourohthree[] = '/admin/structure/block-content/manage/' . $machine_name . '/fields/add-field';
    }
    foreach ($fourohthree as $path) {
      $this->assertForbiddenWithReadOnlyNotice($path);
    }

    // Pages that should be read-only: allowed (200), but with the notice.
    $read_only_paths = [];
    foreach (self::RESTRICTED_NODE_TYPES as $machine_name) {
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/fields';
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/form-display';
      $read_only_paths[] = '/admin/structure/types/manage/' . $machine_name . '/display';
    }
    foreach (self::RESTRICTED_BLOCK_TYPES as $machine_name) {
      $read_only_paths[] = '/admin/structure/block-content/manage/' . $machine_name . '/fields';
    }
    foreach (self::RESTRICTED_MEDIA_TYPES as $machine_name) {
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/fields';
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/form-display';
      $read_only_paths[] = '/admin/structure/media/manage/' . $machine_name . '/display';
    }
    // Image styles provided by the kernel.
    foreach (self::UTEXAS_IMAGE_STYLES as $style) {
      $read_only_paths[] = '/admin/config/media/image-styles/manage/' . $style;
      $read_only_paths[] = '/admin/config/media/image-styles/manage/' . $style . '/delete';
    }

    foreach ($read_only_paths as $path) {
      if ($path === '/admin/structure/media/manage/utexas_document/display') {
        // @todo Skip until #3467501 is fixed (introduced in D10.3).
        continue;
      }
      $this->assertAllowedWithReadOnlyNotice($path);
    }
  }

}
