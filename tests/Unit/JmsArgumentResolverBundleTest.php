<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Solitus0\JmsArgumentResolverBundle\JmsArgumentResolverBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class JmsArgumentResolverBundleTest extends TestCase
{
    public function testExtendsSymfonyBundle(): void
    {
        $bundle = new JmsArgumentResolverBundle();

        self::assertInstanceOf(Bundle::class, $bundle);
    }
}
