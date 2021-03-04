<?php

namespace Drupal\utexas_block_library_access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Controller for the block content permissions.
 */
class AccessControlHandler implements AccessControlHandlerInterface {

  /**
   * The block content types.
   *
   * @var array
   */
  protected $blockContentTypes;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs the block content access control handler instance.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function checkBlockContentAccess($operation) {
    // Access is permitted with either of the listed blocks permissions.
    /** @var \Drupal\Core\Access\AccessResultInterface $result */
    $result = AccessResult::allowedIfHasPermission($this->currentUser, 'administer blocks');
    $result = $result->orIf(AccessResult::allowedIfHasPermission($this->currentUser, 'create and edit reusable blocks'));

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function checkBlockContentTypeAccess($operation) {
    // Only allow view access. View access is needed for the "block type" column
    // when displaying the 'block_content' view.
    if ($operation != 'view') {
      return;
    }
    // Access is permitted with either of the listed blocks permissions.
    /** @var \Drupal\Core\Access\AccessResultInterface $result */
    $result = AccessResult::allowedIfHasPermission($this->currentUser, 'administer blocks');
    $result = $result->orIf(AccessResult::allowedIfHasPermission($this->currentUser, 'create and edit reusable blocks'));

    return $result;
  }

}
