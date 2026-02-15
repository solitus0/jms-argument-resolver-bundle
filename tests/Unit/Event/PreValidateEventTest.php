<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Solitus0\JmsArgumentResolverBundle\Event\PreValidateEvent;

final class PreValidateEventTest extends TestCase
{
    public function testStoresObject(): void
    {
        $object = new \stdClass();
        $event = new PreValidateEvent($object);

        self::assertSame($object, $event->object);
    }
}
