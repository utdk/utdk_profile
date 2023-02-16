<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\filter\Entity\FilterFormat;
use Drupal\node\Entity\Node;

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
    // Check for presence of Social Sharing block.
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Test Page',
    ]);
    $node->save();
    $this->drupalGet("/node/" . $node->id());
    $assert->responseContains('Share this content');

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
        'drupalMedia',
        'blockQuote',
        'insertTable',
        '|',
        'heading',
        '|',
        'specialCharacters',
        'subscript',
        'superscript',
        'underline',
        'sourceEditing',
      ],
    ];
    $this->assertEquals($ckeditor_actual, $ckeditor_expected);
    $allowed_tags = $this->config('filter.format.flex_html')->get('filters.filter_html.settings.allowed_html');
    $tags_to_test = '<a hreflang id role title aria-controls aria-haspopup aria-label aria-expanded aria-selected data-entity-substitution data-entity-type data-entity-uuid data-toggle data-slide media rel target href> <abbr title id role class> <address id role class> <article id role class> <aside id role class> <audio id role autoplay buffered controls loop muted preload src volume class> <blockquote id role class> <br id role class> <button type id role aria-label aria-expanded aria-controls aria-haspopup data-toggle data-target data-dismiss data-placement data-container title class> <caption id role class> <cite title id role class> <code id role class> <col id role class> <colgroup id role class> <del id role class> <details id role class> <dl id role class> <dt id role class> <dd id role class> <div role id aria-label aria-labelledby aria-hidden data-ride data-dismiss data-toggle data-parent data-spy data-offset data-target tabindex class> <drupal-media title data-entity-type data-entity-uuid alt data-view-mode data-caption data-align> <drupal-url data-*> <em id role> <figure id role class> <figcaption id role class> <footer id role class> <header id role class> <img alt height width align id role src data-entity-type data-entity-uuid data-align data-caption title class> <h1 id role class="text-align-left text-align-center text-align-right text-align-justify"> <h2 id role class="text-align-left text-align-center text-align-right text-align-justify"> <h3 id role class="text-align-left text-align-center text-align-right text-align-justify"> <h4 id role class="text-align-left text-align-center text-align-right text-align-justify"> <h5 id role class="text-align-left text-align-center text-align-right text-align-justify"> <h6 id role class="text-align-left text-align-center text-align-right text-align-justify"> <hr id role> <i id role class> <li role id aria-controls aria-current data-slide-to data-target> <mark id role class> <nav id role aria-label class> <ol id role aria-labelledby type start> <p id role class> <pre id role class> <rowspan id role class> <section id role class> <s> <small id role class> <span id role aria-hidden class> <source src type> <strike id role class> <strong id role class> <sub id role> <sup id role> <summary id role class> <time id role class> <table border class id role title> <tbody id role class> <td id role headers title rowspan colspan class> <tfoot id role> <th headers scope id role rowspan colspan class> <thead id role> <tr id role class> <track src sclang label default> <u id role> <ul id role background bgcolor aria-labelledby> <video width height controls autoplay buffered loop muted playsinline poster preload src>';
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
    // $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_qualtrics.status');
    // $this->assertTrue($filter_status);

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
    $testContentEditorUser = $this->initializeContentEditor();
    $this->drupalLogin($testContentEditorUser);
    // Make sure that a Content Editor has default access to the
    // Flex HTML format.
    $flex_html = FilterFormat::load('flex_html');
    $this->assertTrue($flex_html->access('use', $testContentEditorUser), 'A Content Editor has default access to the Flex HTML format.');
    // Make sure that a Content Editor doesn't have access to the
    // Full HTML format.
    $full_html = FilterFormat::load('full_html');
    $this->assertFalse($full_html->access('use', $testContentEditorUser), 'A Content Editor does not have access to the Full HTML format.');
    // Verify that 'Flex HTML' is at the top of the filter_formats list.
    $formats = array_keys(filter_formats());
    $this->assertTrue($formats[0] == 'flex_html', 'Flex HTML is at the top of the filter_formats list.');
    // Make sure a Content Editor doesn't have access to Field UI.
    $this->drupalGet('admin/structure/types/manage/utexas_flex_page/fields');
    $assert->statusCodeEquals(404);
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
    $this->drupalLogin($this->initializeSiteManager());
    // Make sure a Site Manager doesn't have access to the Field UI.
    $this->drupalGet('admin/structure/types/manage/utexas_flex_page/fields');
    $assert->statusCodeEquals(404);
    // Make sure a Site Manager has access to the Block UI.
    $this->drupalGet('admin/structure/block');
    $assert->statusCodeEquals(200);
    // Make sure a Site Manager has access to the Block Content tab.
    $this->drupalGet('/admin/content/block-content');
    $assert->statusCodeEquals(200);
    // Make sure a Site Manager doesn't have access to the permissions page.
    $this->drupalGet('admin/people/permissions');
    $assert->statusCodeEquals(403);
    // Site managers cannot access the Layout Builder Styles configuration page.
    $this->drupalGet('admin/config/content/layout_builder_style');
    $assert->statusCodeEquals(403);

    // Verify demo content renders as expected.
    /** @var \Drupal\Core\Extension\ModuleInstaller $module_installer */
    $module_installer = $this->container->get('module_installer');
    $module_installer->install(['utexas_devel']);
    $this->drupalGet('featured-highlight');
    $featured_highlight_path = 'styles/utexas_image_style_500w/public/generated_sample/tower-lighting.gif';
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
