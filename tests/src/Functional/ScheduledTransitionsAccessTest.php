<?php

declare(strict_types=1);

namespace Drupal\Tests\utexas\Functional;

use Drupal\node\Entity\Node;
use Drupal\scheduled_transitions\ScheduledTransitionsPermissions as Permissions;

/**
 * Tests that the Scheduled Transitions UI is gated by permission.
 *
 * @group utexas
 * @group scheduled_transitions
 */
class ScheduledTransitionsAccessTest extends FunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['scheduled_transitions'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalPlaceBlock('local_tasks_block');

    // The 'page' bundle is on standard_workflow by default (see
    // profile config/install/workflows.workflow.standard_workflow.yml), but
    // Scheduled Transitions' own content-moderation-support access check
    // additionally requires the bundle to be listed here.
    \Drupal::configFactory()->getEditable('scheduled_transitions.settings')
      ->set('bundles', [
        ['entity_type' => 'node', 'bundle' => 'page'],
      ])
      ->save(TRUE);
  }

  /**
   * A user without any scheduling permission sees and can access nothing.
   */
  public function testAccessDeniedWithoutPermission(): void {
    $node = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
      'moderation_state' => 'published',
    ]);
    $node->save();

    $user = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($user);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementTextNotContains('css', '.block-local-tasks-block', 'Scheduled transitions');

    $this->assertForbidden($node->toUrl('scheduled_transitions'));
    $this->assertForbidden($node->toUrl('scheduled_transition_add'));
  }

  /**
   * A user with the bundle-specific permissions sees and can use the UI.
   */
  public function testAccessGrantedWithPermission(): void {
    $node = Node::create([
      'type' => 'page',
      'title' => $this->randomMachineName(),
      'moderation_state' => 'published',
    ]);
    $node->save();

    $user = $this->drupalCreateUser([
      'access content',
      Permissions::viewScheduledTransitionsPermission('node', 'page'),
      Permissions::addScheduledTransitionsPermission('node', 'page'),
    ]);
    $this->drupalLogin($user);

    $this->drupalGet($node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementTextContains('css', '.block-local-tasks-block', 'Scheduled transitions');

    $this->assertAllowed($node->toUrl('scheduled_transitions'));
    $this->assertAllowed($node->toUrl('scheduled_transition_add'));
  }

  /**
   * The sitewide scheduled transitions report requires its own permission.
   */
  public function testAdminCollectionRequiresViewAllPermission(): void {
    $user = $this->drupalCreateUser([]);
    $this->drupalLogin($user);
    $this->assertForbidden('/admin/content/scheduled-transitions');

    $user = $this->drupalCreateUser(['view all scheduled transitions']);
    $this->drupalLogin($user);
    $this->assertAllowed('/admin/content/scheduled-transitions');
  }

}
