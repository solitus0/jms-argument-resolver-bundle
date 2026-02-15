<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class NotNullEntity extends Constraint
{
    public string $message = 'validation.not_null_entity.invalid';
}
