<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\ArgumentResolver;

interface ResolvableValidationGroupsInterface
{
    /**
     * @return array<int, string>
     */
    public function resolveValidationGroups(): array;
}
