<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\simpletest\WebTestBase;

/**
 * Verifies full installation complies with everything enabled while installing.
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
   * Verifies all modules are enabled when doing a site installation.
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
  }

}
