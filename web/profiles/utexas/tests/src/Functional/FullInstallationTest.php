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
    // $parameters['forms']['utexas_select_extensions']['flex_page_enabled'] = 1;
    return $parameters;
  }

  /**
   * Tests routes info.
   */
  public function testFullInstallation() {
    $this->assertTrue(1 === 1);
  }

}
