<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\DTO;

class NullEntity
{
    public function __construct(
        private int|string $id
    ) {
    }

    public function getId(): int|string
    {
        return $this->id;
    }
}
