<?php
/**
 * Unique Login validator.
 */
namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Unique login validator
 * Class UniqueLoginValidator
 * @package Validator\Constraints
 */
class UniqueLoginValidator extends ConstraintValidator
{
    /**
     * Validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint->repository) {
            return;
        }
        $result = $constraint->repository->findForUniqueness(
            $value,
            $constraint->elementId
        );
        if ($result && count($result)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ user }}', $value)
                ->addViolation();
        }
    }
}
