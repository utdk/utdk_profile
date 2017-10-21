<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\simpletest\WebTestBase;

/**
 * Ensures that tests for the UTexas installation profile can run.
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
    $parameters['forms']['utexas_select_extensions']['utexas_enable_flex_page_content_type'] = FALSE;
    $parameters['forms']['utexas_select_extensions']['utexas_enable_fp_editor_role'] = FALSE;
    return $parameters;
  }

  /**
   * Tests routes info.
   */
  public function testMinimalInstallation() {
    // Assert that Flex Page role is disabled.
    $fp_role = \Drupal::moduleHandler()->moduleExists('utexas_role_flex_page_editor');
    $this->assertFalse($fp_role);
    // Assert that Flex Page CT is disabled.
    $fp_ct = \Drupal::moduleHandler()->moduleExists('utexas_content_type_flex_page');
    $this->assertFalse($fp_ct);
    // Assert that LPN is disabled.
    $lpn = \Drupal::moduleHandler()->moduleExists('layout_per_node');
    $this->assertFalse($lpn);
  }

}
