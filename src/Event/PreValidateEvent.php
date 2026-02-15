<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Event;

final class PreValidateEvent
{
    public function __construct(
        public object $object,
    ) {
    }
}
