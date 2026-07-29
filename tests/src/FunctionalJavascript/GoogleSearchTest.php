<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\Tests\utexas\Traits\TextFormatsTestTraits\TextFormatsTestTrait;
use Drupal\user\RoleInterface;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Verifies Flex HTML behavior.
 */
#[RunTestsInSeparateProcesses]
class GoogleSearchTest extends FunctionalJavascriptTestBase {

  use TextFormatsTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalLogin($this->initializeSuperAdminUser());
  }

  /**
   * Test behavior of existing text formats.
   */
  public function testGoogleSearch() {
    $page = $this->getSession()->getPage();
    $assert = $this->assertSession();
    $assert->elementTextContains('css', '.region-header-tertiary', 'Search');
    $page->fillField('google-cse-query', 'Web Content Management Office Hours');
    $this->submitForm([], 'Search');

    $this->assertStringContainsString('/search/google?keys=Web+Content+Management+Office+Hours', $this->getUrl(), 'Site search submits to /search/google');
    $this->assertStringNotContainsString('form_build_id', $this->getUrl(), 'Form build information is omitted from GET parameters per formUtexasSearchFormAlter');
    $assert->pageTextContains("Search for 'Web Content Management Office Hours'");
    $assert->elementTextContains('css', '.gsc-orderby-label', 'Sort by');

    // Simulate an update from a site with Google CSE.
    // @todo Remove this after all sites have updated to 3.32.0.
    $module_installer = $this->container->get('module_installer');
    $module_installer->uninstall(['utexas_google_search']);
    $module_installer->install(['legacy_google_cse']);
    // Add permissions to anonymous role.
    $anon_perms = [
      'access content',
      'search Google CSE',
      'search content',
    ];
    $entity_type_manager = \Drupal::entityTypeManager();
    /** @var Drupal\user\Entity\Role $anon */
    $anon = $entity_type_manager->getStorage('user_role')->load(RoleInterface::ANONYMOUS_ID);
    foreach ($anon_perms as $perm) {
      $anon->grantPermission($perm);
    }
    $anon->save();
    $this->drupalGet('<front>');
    // Confirm Legacy Google CSE search is present in header tertiary.
    $assert->elementTextContains('css', '.region-header-tertiary', 'Search');
    $assert->elementTextContains('css', '.search-block-form', 'Search');
    $module_installer->install(['utexas_google_search']);
    $this->drupalGet('<front>');
    drupal_flush_all_caches();
    // Confirm that the hook_install() for utexas_google_search...
    // (A) Migrates the Google PSE ID.
    $google_pse_id = \Drupal::state()->get('utexas.google_pse_id') ?? '';
    $this->assertEquals('testonlyvalue', $google_pse_id, 'Google PSE ID migrated to the Drupal state value');
    // (B) Removes the legacy search block
    $assert->elementNotExists('css', '.search-block-form');
    // (c) Replaces it with the new Utexas Google Search block
    $assert->elementTextContains('css', '.region-header-tertiary', 'Search');
  }

}
