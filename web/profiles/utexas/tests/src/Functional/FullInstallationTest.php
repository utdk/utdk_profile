<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that tests for the UTexas installation profile can run.
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
   * Tests routes info.
   */
  public function testFullInstallation() {
    // Assert that Flex Page role is enabled.
    $fp_role = \Drupal::moduleHandler()->moduleExists('utexas_role_flex_page_editor');
    $this->assertTrue($fp_role);
    // Assert that Flex Page CT is enabled.
    $fp_ct = \Drupal::moduleHandler()->moduleExists('utexas_content_type_flex_page');
    $this->assertTrue($fp_ct);
    // Assert that LPN is enabled.
    $lpn = \Drupal::moduleHandler()->moduleExists('layout_per_node');
    $this->assertTrue($lpn);
  }

}
