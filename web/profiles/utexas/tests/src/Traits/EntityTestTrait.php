<?php

namespace Drupal\Tests\utexas\Traits;

use Drupal\file\Entity\File;
use \Drupal\node\Entity\Node;

/**
 * General-purpose methods for interacting with Drupal entities.
 */
trait EntityTestTrait {

  /**
   * Asserts the existence of an entity.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param mixed|mixed[] $id
   *   The entity ID, or a set of IDs.
   */
  protected function assertEntityExists($entity_type, $id) {
    $this->assertContainsAll(
      (array) $id,
      \Drupal::entityQuery($entity_type)->execute()
    );
  }

  /**
   * Asserts that a haystack contains a set of needles.
   *
   * @param mixed[] $needles
   *   The needles expected to be in the haystack.
   * @param mixed[] $haystack
   *   The haystack.
   */
  protected function assertContainsAll(array $needles, array $haystack) {
    $diff = array_diff($needles, $haystack);
    $this->assertEmpty($diff);
  }

  /**
   * Creates a test image in Drupal and returns the image URI.
   *
   * @return string
   *    The URI of the newly created file in the Drupal filesystem.
   */
  protected function createTestImage() {
    $images = $this->getTestFiles('image');
    // Create a File entity for the initial image. The zeroth element is a PNG.
    $file = File::create([
      'uri' => $images[0]->uri,
      'uid' => 0,
      'status' => FILE_STATUS_PERMANENT,
    ]);
    $file->save();
    return $images[0]->uri;
  }

  /**
   * Populates & saves a basic page to the database.
   *
   * @return int
   *    The new node's internal ID.
   */
  protected function createBasicPage() {
    $node = Node::create([
      'type'        => 'page',
      'title'       => 'Test Basic Page',
    ]);
    $node->save();
    return $node->id();
  }

}
