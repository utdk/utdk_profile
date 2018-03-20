<?php

namespace Drupal\utexas_paragraph_promo_units\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for changing the menu settings in pending revisions.
 *
 * @Constraint(
 *   id = "PromoUnitItemConstraint",
 *   label = @Translation("Other data besides image style required", context = "Validation"),
 * )
 */
class PromoUnitItemConstraint extends Constraint {

  /**
   * Violation message.
   *
   * @var string
   */
  public $insufficientData = "There is one or more Promo Unit Items with no data entered. Data should be added or these items should be removed.";

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return ['field_utexas_puc_items'];
  }

}
