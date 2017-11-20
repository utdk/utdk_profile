<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * General-purpose methods for setup & installation.
 */
trait InstallTestTrait {

  /**
   * {@inheritdoc}
   */
  protected function installParameters() {
    $parameters = parent::installParameters();
    // Enable all Utexas installation options.
    $parameters['forms']['utexas_select_extensions']['utexas_enable_flex_page_content_type'] = 1;
    $parameters['forms']['utexas_select_extensions']['utexas_enable_fp_editor_role'] = 1;
    return $parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function doSharedSetup() {
    $this->strictConfigSchema = NULL;
  }

}
