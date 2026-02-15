<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use Solitus0\JmsArgumentResolverBundle\DTO\NullEnum;
use Solitus0\JmsArgumentResolverBundle\Util\ArrayPropertyUtil;

class BackedEnumHandler implements SubscribingHandlerInterface
{
    /**
     * @return array<int, array{direction: int, format: string, type: string, method: string}>
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'BackedEnum',
                'method' => 'serialize',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'BackedEnum',
                'method' => 'deserialize',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $type
     */
    public function serialize(
        JsonSerializationVisitor $visitor,
        \BackedEnum $data,
        array $type,
        Context $context
    ): string|int {
        return $data->value;
    }

    /**
     * @param array<string, mixed> $type
     */
    public function deserialize(JsonDeserializationVisitor $visitor, mixed $data, array $type, Context $context): \BackedEnum|NullEnum|null
    {
        $params = ArrayPropertyUtil::getProperty($type, 'params');
        $firstParam = ArrayPropertyUtil::getProperty($params, 0);
        $className = ArrayPropertyUtil::getProperty($firstParam, 'name');

        if (!$className || !enum_exists($className)) {
            return null;
        }

        $reflection = new \ReflectionEnum($className);
        if (!$reflection->isBacked()) {
            return null;
        }
        $backingType = $reflection->getBackingType()->getName(); // 'int' | 'string'

        // Normalize incoming JSON: cast "123" -> 123 for int-backed enums, and scalars to string for string-backed
        if ($backingType === 'int' && is_string($data) && ctype_digit($data)) {
            $data = (int) $data;
        } elseif ($backingType === 'string' && !is_string($data) && is_scalar($data)) {
            $data = (string) $data;
        }

        $typeMatches = ($backingType === 'int' && is_int($data))
            || ($backingType === 'string' && is_string($data));

        if (!$typeMatches) {
            // Not convertible / not a supported type for backed enums -> graceful fallback
            if (!is_scalar($data)) {
                return null;
            }

            return new NullEnum($data);
        }

        /** @var class-string<\BackedEnum> $className */
        $enum = $className::tryFrom($data);
        if (!$enum) {
            return new NullEnum($data);
        }

        return $enum;
    }
}
