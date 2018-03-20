<?php

namespace Drupal\utexas_paragraph_promo_units\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the ProtectedUserFieldConstraint constraint.
 */
class PromoUnitItemConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    foreach ($items as $delta => $item) {
      $cta = $item->entity->get('field_utexas_pu_cta_link')->getValue();
      $copy = $item->entity->get('field_utexas_pu_copy')->getValue();
      $headline = $item->entity->get('field_utexas_pu_headline')->getValue();
      $image = $item->entity->get('field_utexas_pu_image')->getValue();
      if (empty($cta) && empty($copy) && empty($headline) && empty($image)) {
        $this->context->buildViolation($constraint->insufficientData)
          ->atPath((string) $delta)
          ->addViolation();
      }
    }
  }

}
