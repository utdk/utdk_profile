<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\filter\Entity\FilterFormat;
use Drupal\filter\FilterFormatRepositoryInterface;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Verifies full installation completes with everything enabled.
 */
#[RunTestsInSeparateProcesses]
class BaseInstallationTest extends FunctionalTestBase {

  use UserTestTrait;

  /**
   * Verifies that all installation options are checked.
   *
   * When all installation options are checked, all components and optional
   * components are subsequently enabled.
   */
  public function testBaseInstallation() {
    $should_be_enabled = [
      'utexas_block_social_links',
      'utexas_content_type_flex_page',
      'utexas_role_content_editor',
      'utexas_role_site_manager',
      'block',
    ];
    foreach ($should_be_enabled as $module) {
      $module_enabled = \Drupal::moduleHandler()->moduleExists($module);
      $this->assertTrue($module_enabled);
    }
    $should_not_be_enabled = [
      'utexas_devel',
    ];
    foreach ($should_not_be_enabled as $module) {
      $module_enabled = \Drupal::moduleHandler()->moduleExists($module);
      $this->assertFalse($module_enabled);
    }
    // Assert that Forty Acres is the active theme.
    $default_theme = \Drupal::config('system.theme')->get('default');
    $this->assertEquals($default_theme, 'speedway');

    // Assert country and timezone set to US and America/Chicago.
    // $timezone = $this->config('system.date')->get('timezone.default');
    // $country = $this->config('system.date')->get('country.default');
    // $this->assertEquals($timezone, 'America/Chicago');
    // $this->assertEquals($country, 'US');
    //
    // Node rendering (Social Sharing block, flex page meta tags) is covered
    // by the Playwright equivalent's testPageNodeRendersSocialSharingBlock()
    // and testFlexPageNodeRendersExpectedMetaTags()
    // (tests/playwright/BaseInstallationTest.php) — not duplicated here.
    // Assert Flex HTML elements are default values.
    $ckeditor_actual = $this->config('editor.editor.flex_html')->get('settings.toolbar');
    $ckeditor_expected = [
      'items' => [
        'bold',
        'italic',
        'strikethrough',
        'horizontalLine',
        'removeFormat',
        'undo',
        'redo',
        '|',
        'link',
        '|',
        'bulletedList',
        'numberedList',
        'outdent',
        'indent',
        'alignment',
        '|',
        'insertTable',
        'drupalMedia',
        'urlembed',
        'blockQuote',
        'heading',
        'style',
        '|',
        'specialCharacters',
        'subscript',
        'superscript',
        'underline',
        'sourceEditing',
        '-',
      ],
    ];
    $this->assertEquals($ckeditor_actual, $ckeditor_expected);
    $allowed_tags = $this->config('filter.format.flex_html')->get('filters.filter_html.settings.allowed_html');
    $tags_to_test = '<a href hreflang class id name role title aria-controls aria-haspopup aria-label aria-expanded aria-selected data-* media rel target> <abbr title class id role> <address class id role> <article class id role> <aside class id role> <audio class id role autoplay buffered controls loop muted preload src volume> <blockquote class id role> <br class id role> <button type class id role aria-label aria-expanded aria-controls aria-haspopup data-* title> <caption class id role> <cite title class id role> <code class id role> <col class id role> <colgroup class id role> <del class id role> <details class id role> <dl class id role> <dt class id role> <dd class id role> <div role class id aria-label aria-labelledby aria-hidden data-* tabindex> <drupal-url data-*> <drupal-media data-* alt title> <em class id role> <figure class id role> <figcaption class id role> <footer class id role> <header class id role> <hr class id role> <h1 class id role> <h2 class id role> <h3 class id role> <h4 class id role> <h5 class id role> <h6 class id role> <img alt height width align class id role src data-* title> <i class id role> <li role class id aria-controls aria-current data-*> <mark class id role> <nav class id role aria-label> <ol class id role aria-labelledby start type> <p class id role> <pre class id role> <q class id cite> <rowspan class id role> <s class id role> <section class id role> <small class id role> <span class id role aria-hidden> <source src type> <strike class id role> <strong class id role> <sub class id role> <summary class id role> <sup class id role> <table border class id role title> <tbody class id role> <td class id role colspan rowspan headers title> <tfoot class id role> <th colspan rowspan headers scope class id role> <thead class id role> <time class id role> <tr class id role> <track src sclang label default> <u class id role> <ul class id role background bgcolor aria-labelledby> <video width height controls autoplay buffered loop muted playsinline poster preload src>';
    $this->assertEquals($tags_to_test, $allowed_tags);
    // Assert Flex HTML filter are enabled.
    $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_autop.status');
    $this->assertFalse($filter_status);
    $filter_status = $this->config('filter.format.flex_html')->get('filters.media_embed.status');
    $this->assertTrue($filter_status);
    $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_url.status');
    $this->assertTrue($filter_status);
    $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_iframe_title.status');
    $this->assertTrue($filter_status);
    $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_htmlcorrector.status');
    $this->assertTrue($filter_status);
    $filter_status = $this->config('filter.format.flex_html')->get('filters.linkit.status');
    $this->assertTrue($filter_status);
    $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_responsive_tables_filter.status');
    $this->assertTrue($filter_status);
    $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_pathologic.status');
    $this->assertTrue($filter_status);
    $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_url.status');
    $this->assertTrue($filter_status);
    $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_qualtrics.status');
    $this->assertTrue($filter_status);

    // Verify that the 'Restricted HTML' text format is present.
    $filter_html_filter = $this->config('filter.format.restricted_html')->get('format');
    $this->assertEquals($filter_html_filter, 'restricted_html');
    // Verify that the 'Full HTML' text format is present.
    $filter_html_filter = $this->config('editor.editor.full_html')->get('format');
    $this->assertEquals($filter_html_filter, 'full_html');
    // Verify that the 'Basic HTML' text format is present.
    $filter_html_filter = $this->config('filter.format.basic_html')->get('format');
    $this->assertEquals($filter_html_filter, 'basic_html');
    // Assert default language set to English.
    $language = $this->config('system.site')->get('langcode');
    $this->assertEquals($language, 'en');
    $default_language = $this->config('system.site')->get('default_langcode');
    $this->assertEquals($default_language, 'en');

    // Verify default metatag configuration.
    $expected_metatag_defaults = [
      'canonical_url' => '[current-page:url]',
      'title' => '[current-page:title] | [site:name]',
      'og_title' => '[current-page:title]',
      'og_type' => 'website',
      'og_updated_time' => '[node:changed:custom:c]',
      'og_url' => '[current-page:url]',
      'twitter_cards_title' => '[current-page:title]',
      'twitter_cards_type' => 'summary',
    ];
    $actual_metatag_defaults = $this->config('metatag.metatag_defaults.global')->get('tags');
    $og_image = $actual_metatag_defaults['og_image'];
    unset($actual_metatag_defaults['og_image']);
    $this->assertEquals($actual_metatag_defaults, $expected_metatag_defaults);
    $this->assertStringContainsString('ut_tower.jpg', $og_image);

    // Test Content Editor Role permissions.
    //
    // Menu link icon/target behavior is covered by the Playwright
    // equivalent's testMainMenuLinkTargetBehavior()
    // (tests/playwright/BaseInstallationTest.php) — not duplicated here.
    // $testContentEditorUser is still created here (without a Mink login,
    // which isn't needed) because FilterFormat::access() below is a pure
    // permission-system check, not page content.
    $testContentEditorUser = $this->initializeContentEditor();

    // Make sure that a Content Editor has default access to the
    // Flex HTML format.
    $flex_html = FilterFormat::load('flex_html');
    $this->assertTrue($flex_html->access('use', $testContentEditorUser), 'A Content Editor has default access to the Flex HTML format.');
    // Make sure that a Content Editor doesn't have access to the
    // Full HTML format.
    $full_html = FilterFormat::load('full_html');
    $this->assertFalse($full_html->access('use', $testContentEditorUser), 'A Content Editor does not have access to the Full HTML format.');
    // Verify that 'Flex HTML' is at the top of the filter_formats list.
    $formats = array_keys(\Drupal::service(FilterFormatRepositoryInterface::class)->getAllFormats());
    $this->assertTrue($formats[0] == 'flex_html', 'Flex HTML is at the top of the filter_formats list.');
    //
    // Content Editor HTTP-access status codes (Field UI, Block UI, Block
    // Content), the entire Site Manager role test (permission assignment
    // is now driven via the real /admin/config/content/utexas/permissions
    // form — see ensureRoleAccountsConfigured() in the Playwright
    // equivalent), and demo content rendering (after enabling
    // utexas_devel) are all covered by testContentEditorRoleAccess(),
    // testSiteManagerRoleAccess(), and testDemoContentRendersAsExpected()
    // (tests/playwright/BaseInstallationTest.php) — not duplicated here.
  }

  /**
   * Using trait from FunctionalTestSetupTrait.php.
   */
  protected function initConfig(ContainerInterface $container) {
  }

}
