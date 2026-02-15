<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Util;

class ArrayPropertyUtil
{
    public static function getProperty(mixed $data, string|int $key, mixed $default = null): mixed
    {
        if (!is_array($data)) {
            return $default;
        }

        return (array_key_exists($key, $data) && null !== $data[$key]) ? $data[$key] : $default;
    }
}
