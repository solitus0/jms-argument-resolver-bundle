<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Validator;

use Solitus0\JmsArgumentResolverBundle\DTO\NullEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NotNullEnumValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotNullEnum) {
            return;
        }

        if ($value instanceof NullEnum) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', (string) $value->getValue())
                ->addViolation()
            ;
        }
    }
}
