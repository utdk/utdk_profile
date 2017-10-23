<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\simpletest\WebTestBase;

/**
 * Verifies minimal installation complies with nothing enabled while installing.
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
  }

}
