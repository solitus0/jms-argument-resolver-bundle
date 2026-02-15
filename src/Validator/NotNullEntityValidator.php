<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Validator;

use Solitus0\JmsArgumentResolverBundle\DTO\NullEntity;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotNullEntityValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotNullEntity) {
            return;
        }

        if ($value instanceof NullEntity) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ id }}', (string) $value->getId())
                ->addViolation()
            ;
        }
    }
}
