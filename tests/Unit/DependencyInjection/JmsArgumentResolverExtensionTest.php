<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Solitus0\JmsArgumentResolverBundle\ArgumentResolver\QueryParameterValueResolver;
use Solitus0\JmsArgumentResolverBundle\DependencyInjection\JmsArgumentResolverExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class JmsArgumentResolverExtensionTest extends TestCase
{
    public function testLoadsServiceConfiguration(): void
    {
        $container = new ContainerBuilder();
        $extension = new JmsArgumentResolverExtension();

        $extension->load([], $container);

        self::assertTrue($container->hasDefinition(QueryParameterValueResolver::class));
    }
}
