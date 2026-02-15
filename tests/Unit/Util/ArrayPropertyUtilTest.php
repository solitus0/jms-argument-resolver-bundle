<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Solitus0\JmsArgumentResolverBundle\Util\ArrayPropertyUtil;

final class ArrayPropertyUtilTest extends TestCase
{
    public function testReturnsDefaultWhenDataIsNotArray(): void
    {
        $result = ArrayPropertyUtil::getProperty('not-array', 'key', 'default');

        self::assertSame('default', $result);
    }

    public function testReturnsDefaultWhenKeyMissingOrNull(): void
    {
        $missing = ArrayPropertyUtil::getProperty(['a' => 1], 'b', 'default');
        $nullValue = ArrayPropertyUtil::getProperty(['a' => null], 'a', 'default');

        self::assertSame('default', $missing);
        self::assertSame('default', $nullValue);
    }

    public function testReturnsValueWhenPresentAndNotNull(): void
    {
        $value = ArrayPropertyUtil::getProperty(['a' => 0], 'a', 'default');

        self::assertSame(0, $value);
    }
}
