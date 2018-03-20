<?php

namespace Drupal\utexas_paragraph_promo_units\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for changing the menu settings in pending revisions.
 *
 * @Constraint(
 *   id = "PromoUnitContainerConstraint",
 *   label = @Translation("Missing data", context = "Validation"),
 * )
 */
class PromoUnitContainerConstraint extends Constraint {

  /**
   * Violation message.
   *
   * @var string
   */
  public $insufficientData = "There is a Promo Unit Container title with no value entered. This Promo Unit Container should be removed or Promo Unit Items should be added.";

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return ['field_utexas_puc_title'];
  }

}
