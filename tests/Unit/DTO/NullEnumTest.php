<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Solitus0\JmsArgumentResolverBundle\DTO\NullEnum;

final class NullEnumTest extends TestCase
{
    public function testReturnsValue(): void
    {
        $enum = new NullEnum('value');

        self::assertSame('value', $enum->getValue());
    }
}
