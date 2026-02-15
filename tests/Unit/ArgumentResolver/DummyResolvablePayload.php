<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Tests\Unit\ArgumentResolver;

use Solitus0\JmsArgumentResolverBundle\ArgumentResolver\ResolvableValidationGroupsInterface;

final class DummyResolvablePayload implements ResolvableValidationGroupsInterface
{
    public function resolveValidationGroups(): array
    {
        return ['Extra'];
    }
}
