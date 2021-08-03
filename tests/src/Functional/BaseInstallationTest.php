<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Verifies full installation completes with everything enabled.
 *
 * @group utexas
 */
class BaseInstallationTest extends BrowserTestBase {
  use InstallTestTrait;
  use EntityTestTrait;
  use UserTestTrait;

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->utexasSharedSetup();
    parent::setUp();
  }

  /**
   * Verifies that all installation options are checked.
   *
   * When all installation options are checked, all components and optional
   * components are subsequently enabled.
   */
  public function testBaseInstallation() {
    $assert = $this->assertSession();
    $should_be_enabled = [
      'utexas_block_social_links',
      'utexas_content_type_flex_page',
      'utexas_role_content_editor',
      'field_ui',
      'block',
    ];
    foreach ($should_be_enabled as $module) {
      $module_enabled = \Drupal::moduleHandler()->moduleExists($module);
      $this->assertTrue($module_enabled);
    }
    $should_not_be_enabled = [
      'utexas_role_site_manager',
      'utexas_devel',
    ];
    foreach ($should_not_be_enabled as $module) {
      $module_enabled = \Drupal::moduleHandler()->moduleExists($module);
      $this->assertFalse($module_enabled);
    }
    // Assert that Forty Acres is the active theme.
    $default_theme = \Drupal::config('system.theme')->get('default');
    $this->assertEquals($default_theme, 'forty_acres');

    // Assert country and timezone set to US and America/Chicago.
    // $timezone = $this->config('system.date')->get('timezone.default');
    // $country = $this->config('system.date')->get('country.default');
    // $this->assertEquals($timezone, 'America/Chicago');
    // $this->assertEquals($country, 'US');
    // Assert Flex HTML elements are default values.
    $ckeditor_actual = $this->config('editor.editor.flex_html')->get('settings.toolbar');
    $ckeditor_expected = [
      'rows' => [
        0 => [
          0 => [
            'name' => 'Formatting',
            'items' => [
              0 => 'Bold',
              1 => 'Italic',
              2 => 'Underline',
              3 => 'Strike',
              4 => 'HorizontalRule',
              5 => 'SpecialChar',
              6 => 'Superscript',
              7 => 'Subscript',
              8 => 'RemoveFormat',
            ],
          ],
          1 => [
            'name' => 'Linking',
            'items' => [
              0 => 'DrupalLink',
              1 => 'DrupalUnlink',
            ],
          ],
          2 => [
            'name' => 'Lists',
            'items' => [
              0 => 'BulletedList',
              1 => 'NumberedList',
              2 => 'Outdent',
              3 => 'Indent',
            ],
          ],
          3 => [
            'name' => 'Media',
            'items' => [
              0 => 'DrupalMediaLibrary',
              1 => 'url',
              2 => 'Blockquote',
              3 => 'Table',
            ],
          ],
          4 => [
            'name' => 'Block Formatting',
            'items' => [
              0 => 'Format',
              1 => 'JustifyLeft',
              2 => 'JustifyCenter',
              3 => 'JustifyRight',
              4 => 'JustifyBlock',
            ],
          ],
          5 => [
            'name' => 'Tools',
            'items' => [
              0 => 'Source',
              1 => 'qualtricsbutton',
            ],
          ],
          6 => [
            'name' => 'Editing',
            'items' => [
              0 => 'Undo',
              1 => 'Redo',
              2 => 'Cut',
              3 => 'Copy',
              4 => 'Paste',
              5 => 'PasteText',
              6 => 'PasteFromWord',
              7 => 'Maximize',
            ],
          ],
        ],
      ],
    ];
    $this->assertEquals($ckeditor_actual, $ckeditor_expected);
    $allowed_tags = $this->config('filter.format.flex_html')->get('filters.filter_html.settings.allowed_html');
    $tags_to_test = '<a href hreflang class id role title aria-controls aria-haspopup aria-label aria-expanded aria-selected data-entity-substitution data-entity-type data-entity-uuid data-toggle data-slide media rel target> <abbr title class id role> <address class id role> <article class id role> <aside class id role> <audio class id role autoplay buffered controls loop muted preload src volume> <blockquote class id role> <br class id role> <button type class id role aria-label aria-expanded aria-controls aria-haspopup data-toggle data-target data-dismiss data-placement data-container title> <caption class id role> <cite title class id role> <code class id role> <col class id role> <colgroup class id role> <del class id role> <details class id role> <dl class id role> <dt class id role> <dd class id role> <div role class id aria-label aria-labelledby aria-hidden data-ride data-dismiss data-toggle data-parent data-spy data-offset data-target tabindex> <drupal-url data-*> <drupal-media data-entity-type data-entity-uuid data-view-mode data-align data-caption alt title> <em class id role> <figure class id role> <figcaption class id role> <footer class id role> <header class id role> <hr class id role> <h1 class id role> <h2 class id role> <h3 class id role> <h4 class id role> <h5 class id role> <h6 class id role> <img alt height width align class id role src data-entity-type data-entity-uuid data-align data-caption title> <i class id role> <li role class id aria-controls aria-current data-slide-to data-target> <mark class id role> <nav class id role aria-label> <ol class id role aria-labelledby start type> <p class id role> <pre class id role> <rowspan class id role> <section class id role> <small class id role> <span class id role aria-hidden> <source src type> <strike class id role> <strong class id role> <sub class id role> <summary class id role> <sup class id role> <table border class id role title> <tbody class id role> <td class id role colspan rowspan headers title> <tfoot class id role> <th colspan rowspan headers scope class id role> <thead class id role> <time class id role> <tr class id role> <track src sclang label default> <u class id role> <ul class id role background bgcolor aria-labelledby> <video width height controls autoplay buffered loop muted playsinline poster preload src>';
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
      'title' => '[current-page:title] | [site:name]',
      'canonical_url' => '[current-page:url]',
      'twitter_cards_page_url' => '[current-page:url] ',
      'twitter_cards_title' => '[current-page:title] | [site:name]',
    ];
    $actual_metatag_defaults = $this->config('metatag.metatag_defaults.global')->get('tags');
    $this->assertEquals($actual_metatag_defaults, $expected_metatag_defaults);

    // Test Content Editor Role permissions.
    $this->initializeContentEditor();
    // @todo investigate why this addRole apparently needs to happen here,
    // in addition to the one defined in initializeContentEditor @jmf3658.
    $this->testUser->addRole('utexas_content_editor');
    $this->testUser->save();
    $this->drupalLogin($this->testUser);
    // Make sure that a Content Editor has default access to the
    // Flex HTML format.
    $flex_html = FilterFormat::load('flex_html');
    $this->assertTrue($flex_html->access('use', $this->testUser), 'A Content Editor has default access to the Flex HTML format.');
    // Make sure that a Content Editor doesn't have access to the
    // Full HTML format.
    $full_html = FilterFormat::load('full_html');
    $this->assertFalse($full_html->access('use', $this->testUser), 'A Content Editor does not have access to the Full HTML format.');
    // Verify that 'Flex HTML' is at the top of the filter_formats list.
    $formats = array_keys(filter_formats());
    $this->assertTrue($formats[0] == 'flex_html', 'Flex HTML is at the top of the filter_formats list.');
    // Make sure a Content Editor doesn't have access to Field UI.
    $this->drupalGet('admin/structure/types/manage/utexas_flex_page/fields');
    $assert->statusCodeEquals(403);
    // Make sure a Content Editor doesn't have access to Block UI.
    $this->drupalGet('admin/structure/block');
    $assert->statusCodeEquals(403);
    // Make sure a Content Editor has access to Block Content tab.
    $this->drupalGet('/admin/content/block-content');
    $assert->statusCodeEquals(200);
    // Make sure a Content Editor has access to create Block Content.
    $this->drupalGet('/block/add');
    $assert->statusCodeEquals(200);

    // Test Site Manager Role permissions.
    $this->initializeSiteManager();
    // Make sure a Site Manager doesn't have access to the Field UI.
    $this->drupalGet('admin/structure/types/manage/utexas_flex_page/fields');
    $assert->statusCodeEquals(403);
    // Make sure a Site Manager doesn't have access to the Block UI.
    $this->drupalGet('admin/structure/block');
    $assert->statusCodeEquals(403);
    // Make sure a Site Manager has access to the Block Content tab.
    $this->drupalGet('/admin/content/block-content');
    $assert->statusCodeEquals(200);
    // Make sure a Site Manager doesn't have access to the permissions page.
    $this->drupalGet('admin/people/permissions');
    $assert->statusCodeEquals(403);
    // Site managers cannot access the Layout Builder Styles configuration page.
    $this->drupalGet('admin/config/content/layout_builder_style/config');
    $assert->statusCodeEquals(403);

    // Verify demo content renders as expected.
    \Drupal::service('module_installer')->install(['utexas_devel']);
    $this->drupalGet('featured-highlight');
    $featured_highlight_path = 'styles/utexas_image_style_500w_300h/public/generated_sample/tower-lighting.gif';
    $assert->elementAttributeContains('css', '.utexas-featured-highlight .image-wrapper img', 'src', $featured_highlight_path);
    $assert->elementTextContains('css', 'h2.ut-headline a', 'Featured Highlight');
    $assert->elementTextContains('css', '.utexas-featured-highlight .ut-copy', 'Add descriptive text to provide a short summary of this featured content.');
    $assert->elementTextContains('css', '.utexas-featured-highlight a.ut-btn', 'Visit UTexas');
    $assert->pageTextContains('June 12, 2019');

    $this->drupalGet('flex-content-area');
    $flex_content_area_path = 'styles/utexas_image_style_340w_227h/public/generated_sample/tower-lighting.gif';
    $assert->elementAttributeContains('css', '.ut-flex-content-area .image-wrapper img', 'src', $flex_content_area_path);
    $assert->elementTextContains('css', '.ut-flex-content-area h3.ut-headline a', 'Flex Content Area 1');
    $assert->elementTextContains('css', '.ut-flex-content-area .ut-copy', 'The Flex Content Area has a number of display options.');
    $assert->elementTextContains('css', '.ut-flex-content-area a.ut-btn', 'Visit UTexas');

    $this->drupalGet('promo-list');
    $promo_list_path = 'styles/utexas_image_style_64w_64h/public/generated_sample/tower-lighting.gif';
    $assert->elementAttributeContains('css', '.promo-list .image-wrapper img', 'src', $promo_list_path);
    $assert->elementTextContains('css', '.utexas-promo-list-container h3.ut-headline--underline', 'Promo List Group 1');
    $assert->elementTextContains('css', '.promo-list .content', 'Short descriptive text can be formatted.');

    $this->drupalGet('promo-unit');
    $promo_unit_path = 'styles/utexas_image_style_176w_112h/public/generated_sample/tower-lighting.gif';
    $assert->elementAttributeContains('css', '.utexas-promo-unit .image-wrapper img', 'src', $promo_unit_path);
    $assert->elementTextContains('css', '.utexas-promo-unit-container h3.ut-headline--underline', 'Promo Unit Group 1');
    $assert->elementTextContains('css', '.utexas-promo-unit .data-wrapper p', 'Short descriptive text can be formatted.');

    $this->drupalGet('photo-content-area');
    $photo_content_area_path = 'styles/utexas_image_style_450w_600h/public/generated_sample/tower-lighting.gif';
    $assert->elementAttributeContains('css', '.ut-photo-content-area .photo-wrapper img', 'src', $photo_content_area_path);
    $assert->elementTextContains('css', '.ut-photo-content-area h2.ut-headline', 'Photo Content Area');
    $assert->elementTextContains('css', '.ut-photo-content-area .ut-copy p', 'Photo content Areas include image, headline, credit, copy text, and links.');

    $this->drupalGet('hero-default');
    $hero_path = 'styles/utexas_image_style_720w_389h/public/generated_sample/tower-lighting.gif';
    $assert->elementAttributeContains('css', '.ut-hero img', 'src', $hero_path);
    $assert->elementTextContains('css', '.hero--caption-credit-wrapper .credit', 'Copyright University of Texas at Austin');
    $assert->elementTextContains('css', '.hero--caption-credit-wrapper .hero-caption', 'A short caption may be added, describing the hero');

    $this->drupalGet('quick-links');
    $assert->elementTextContains('css', '.utexas-quick-links h3.ut-headline', 'Quick Links');
    $assert->elementTextContains('css', '.utexas-quick-links .ut-copy p', 'Quick links include a headline, copy text, and links.');
    $assert->elementTextContains('css', '.utexas-quick-links .link-list a', 'Our commitment to diversity');

    $this->drupalGet('resources');
    $resource_image_path = 'styles/utexas_image_style_400w_250h/public/generated_sample/tower-lighting.gif';
    $assert->elementAttributeContains('css', '.utexas-resource .image-wrapper img', 'src', $resource_image_path);
    $assert->elementTextContains('css', '.ut-resources-wrapper h3.ut-headline--underline', 'Resource Group 1');
    $assert->elementTextContains('css', '.utexas-resource-items .utexas-resource h3.ut-headline', 'Resource 1');

  }

  /**
   * Using trait from FunctionalTestSetupTrait.php.
   */
  protected function initConfig(ContainerInterface $container) {
  }

}
