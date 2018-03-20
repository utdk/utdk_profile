<?php

namespace Drupal\utexas_paragraph_promo_units\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ProtectedUserFieldConstraint constraint.
 */
class PromoUnitContainerConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if ($items->isEmpty() && $items->getParent()->get('field_utexas_puc_items')->isEmpty()) {
      $this->context->addViolation($constraint->insufficientData);
    }
  }

}
