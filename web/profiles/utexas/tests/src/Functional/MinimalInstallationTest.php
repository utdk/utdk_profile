<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\simpletest\WebTestBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Verifies minimal installation completes with nothing enabled.
 *
 * @group utexas
 */
class MinimalInstallationTest extends WebTestBase {

  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

  /**
   * {@inheritdoc}
   */
  protected function installParameters() {
    $parameters = parent::installParameters();
    // Add specific installation form parameters here, e.g.:
    $parameters['forms']['utexas_select_extensions']['utexas_enable_flex_page_content_type'] = NULL;
    $parameters['forms']['utexas_select_extensions']['utexas_enable_fp_editor_role'] = NULL;
    return $parameters;
  }

  /**
   * Verifies that all installation options are unchecked.
   *
   * When all installation options are unchecked, no components or optional
   * components are subsequently enabled.
   */
  public function testMinimalInstallation() {
    $modules = [
      'utexas_role_flex_page_editor',
      'utexas_content_type_flex_page',
      'layout_per_node',
    ];
    foreach ($modules as $module) {
      $module_enabled = \Drupal::moduleHandler()->moduleExists($module);
      $this->assertFalse($module_enabled);
    }
    // Assert country and timezone set to US and America/Chicago.
    $timezone = $this->config('system.date')->get('timezone.default');
    $country = $this->config('system.date')->get('country.default');
    $this->assertEqual($timezone, 'America/Chicago');
    $this->assertEqual($country, 'US');
    // Assert basic html elements are default values.
    $allowed_tags = $this->config('filter.format.basic_html')->get('filters.filter_html.settings.allowed_html');
    $tags_to_test = '<a> <abbr> <address> <article> <aside> <blockquote> <br> <button> <caption> <cite> <code> <del> <details> <dl> <dt> <dd> <div> <em> <figure> <figcaption> <img> <i> <input> <hr> <h1> <h2> <h3> <h4> <h5> <h6> <ul> <ol> <li> <mark> <nav> <p> <pre> <sub> <sup> <table> <th> <tr> <td> <thead> <tbody> <tfoot> <section> <span> <source> <strong> <time> <track> <video>';
    $this->assertEqual($allowed_tags, $tags_to_test);
  }

  /**
   * Using trait from FunctionalTestSetupTrait.php.
   */
  protected function initConfig(ContainerInterface $container) {
  }

}
