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
class FullInstallationTest extends BrowserTestBase {
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
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->utexasSharedSetup();
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  protected function installParameters() {
    $parameters = parent::installParameters();
    // Add specific installation form parameters here, e.g.:
    $parameters['forms']['utexas_select_extensions']['utexas_enable_flex_page_content_type'] = 1;
    $parameters['forms']['utexas_select_extensions']['utexas_enable_fp_editor_role'] = 1;
    $parameters['forms']['utexas_select_extensions']['utexas_enable_social_links'] = 1;
    return $parameters;
  }

  /**
   * Verifies that all installation options are checked.
   *
   * When all installation options are checked, all components and optional
   * components are subsequently enabled.
   */
  public function testFullInstallation() {
    $modules = [
      'utexas_block_social_links',
      'utexas_content_type_flex_page',
      'utexas_role_flex_page_editor',
    ];
    foreach ($modules as $module) {
      $module_enabled = \Drupal::moduleHandler()->moduleExists($module);
      $this->assertTrue($module_enabled);
    }
    // Assert country and timezone set to US and America/Chicago.
    $timezone = $this->config('system.date')->get('timezone.default');
    $country = $this->config('system.date')->get('country.default');
    $this->assertEqual($timezone, 'America/Chicago');
    $this->assertEqual($country, 'US');
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
              0 => 'Blockquote',
              1 => 'DrupalImage',
              2 => 'Table',
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
    $this->assertEqual($ckeditor_actual, $ckeditor_expected);
    $allowed_tags = $this->config('filter.format.flex_html')->get('filters.filter_html.settings.allowed_html');
    $tags_to_test = '<a href hreflang class id role title aria-controls aria-haspopup aria-label aria-expanded aria-selected data-toggle data-slide media rel target> <abbr title class id role> <address class id role> <article class id role> <aside class id role> <audio class id role autoplay buffered controls loop muted preload src volume> <blockquote class id role> <br class id role> <button type class id role aria-label aria-expanded aria-controls aria-haspopup data-toggle data-target data-dismiss data-placement data-container> <caption class id role> <cite title class id role> <code class id role> <col class id role> <colgroup class id role> <del class id role> <details class id role> <dl class id role> <dt class id role> <dd class id role> <div role class id aria-label aria-labelledby aria-hidden data-ride data-dismiss data-toggle data-parent data-spy data-offset data-target tabindex> <em class id role> <figure class id role> <figcaption class id role> <footer class id role> <header class id role> <hr class id role> <h1 class id role> <h2 class id role> <h3 class id role> <h4 class id role> <h5 class id role> <h6 class id role> <img alt height width align class id role src data-entity-type data-entity-uuid data-align data-caption title> <i class id role> <li role class id aria-controls aria-current data-slide-to data-target> <mark class id role> <nav class id role aria-label> <ol class id role aria-labelledby> <p class id role> <pre class id role> <rowspan class id role> <section class id role> <small class id role> <span class id role aria-hidden> <source src type> <strike class id role> <strong class id role> <sub class id role> <summary class id role> <sup class id role> <table class id role title> <tbody class id role> <td class id role colspan headers title> <tfoot class id role> <th colspan headers scope class id role> <thead class id role> <time class id role> <tr class id role> <track src sclang label default> <ul class id role background bgcolor aria-labelledby> <video width height controls autoplay buffered loop muted playsinline poster preload src>';
    $this->assertEqual($allowed_tags, $tags_to_test);
    // Assert Flex HTML filter are enabled.
    $filter_status = $this->config('filter.format.flex_html')->get('filters.filter_autop.status');
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
    $this->assertEqual($filter_html_filter, 'restricted_html');
    // Verify that the 'Full HTML' text format is present.
    $filter_html_filter = $this->config('editor.editor.full_html')->get('format');
    $this->assertEqual($filter_html_filter, 'full_html');
    // Verify that the 'Basic HTML' text format is present.
    $filter_html_filter = $this->config('filter.format.basic_html')->get('format');
    $this->assertEqual($filter_html_filter, 'basic_html');
    // Assert default language set to English.
    $language = $this->config('system.site')->get('langcode');
    $this->assertEqual($language, 'en');
    $default_language = $this->config('system.site')->get('default_langcode');
    $this->assertEqual($default_language, 'en');

    // Test Flex Page Editor Role permissions.
    $this->initializeFlexPageEditor();
    // @todo: investigate why this addRole apparently needs to happen here,
    // in addition to the one defined in initializeFlexPageEditor @jmf3658.
    $this->testUser->addRole('utexas_flex_page_editor');
    $this->testUser->save();
    $this->drupalLogin($this->testUser);
    // Make sure that a Flex Page Editor has default access to the
    // Flex HTML format.
    $flex_html = FilterFormat::load('flex_html');
    $this->assertTrue($flex_html->access('use', $this->testUser), 'A Flex Page Editor has default access to the Flex HTML format.');
    // Make sure that a Flex Page Editor doesn't have access to the
    // Full HTML format.
    $full_html = FilterFormat::load('full_html');
    $this->assertFalse($full_html->access('use', $this->testUser), 'A Flex Page Editor does not have access to the Full HTML format.');
    // Verify that 'Flex HTML' is at the top of the filter_formats list.
    $formats = array_keys(filter_formats());
    $this->assertTrue($formats[0] == 'flex_html', 'Flex HTML is at the top of the filter_formats list.');
  }

  /**
   * Using trait from FunctionalTestSetupTrait.php.
   */
  protected function initConfig(ContainerInterface $container) {
  }

}
