<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\simpletest\WebTestBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Verifies full installation completes with everything enabled.
 *
 * @group utexas
 */
class FullInstallationTest extends WebTestBase {

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
    $parameters['forms']['utexas_select_extensions']['utexas_enable_flex_page_content_type'] = 1;
    $parameters['forms']['utexas_select_extensions']['utexas_enable_fp_editor_role'] = 1;
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
      'utexas_role_flex_page_editor',
      'utexas_content_type_flex_page',
      'layout_per_node',
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
    // Assert default language set to English.
    $language = $this->config('system.site')->get('langcode');
    $this->assertEqual($language, 'en');
    $default_language = $this->config('system.site')->get('default_langcode');
    $this->assertEqual($default_language, 'en');

  }

  /**
   * Using trait from FunctionalTestSetupTrait.php.
   */
  protected function initConfig(ContainerInterface $container) {
  }

}
