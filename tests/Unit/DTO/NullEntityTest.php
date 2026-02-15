<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Solitus0\JmsArgumentResolverBundle\DTO\NullEntity;

final class NullEntityTest extends TestCase
{
    public function testReturnsId(): void
    {
        $entity = new NullEntity(123);

        self::assertSame(123, $entity->getId());
    }
}
