<?php

namespace Drupal\utexas_block_library_access;

/**
 * Block content access control methods.
 */
interface AccessControlHandlerInterface {

  /**
   * Performs access checks for block_content entities.
   *
   * @param string $operation
   *   The entity operation. Usually one of 'view', 'view label', 'update' or
   *   'delete'.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkBlockContentAccess($operation);

  /**
   * Performs access checks for block_content_type entities.
   *
   * @param string $operation
   *   The entity operation. Usually one of 'view', 'view label', 'update' or
   *   'delete'.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkBlockContentTypeAccess($operation);

}
