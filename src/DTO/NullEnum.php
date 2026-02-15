<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\DTO;

class NullEnum
{
    public function __construct(
        private int|string $value
    ) {
    }

    public function getValue(): int|string
    {
        return $this->value;
    }
}
