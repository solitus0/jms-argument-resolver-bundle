<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use Solitus0\JmsArgumentResolverBundle\DTO\NullEntity;
use Solitus0\JmsArgumentResolverBundle\Util\ArrayPropertyUtil;

class UuidToEntityHandler implements SubscribingHandlerInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'UuidToEntity',
                'method' => 'deserialize',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => 'UuidToEntityArray',
                'method' => 'deserializeArray',
            ],
        ];
    }

    /**
     * @param array<int, mixed> $ids
     * @param array<string, mixed> $type
     *
     * @return array<int, object>
     */
    public function deserializeArray(JsonDeserializationVisitor $visitor, array $ids, array $type): array
    {
        if (!$ids) {
            return [];
        }

        $property = $this->getProperty($type);

        $ids = array_map(static function ($item) use ($property) {
            if (is_array($item)) {
                $value = ArrayPropertyUtil::getProperty($item, $property);
                if ($value) {
                    return $value;
                }
            }

            return $item;
        }, $ids);

        $entityClass = $this->getClass($type);
        if ($entityClass === null) {
            return [];
        }
        /** @var class-string $entityClass */
        $repository = $this->entityManager->getRepository($entityClass);

        $result = [];
        foreach ($ids as $id) {
            $object = $this->getObject($repository, $id, $property);
            $result[] = $object;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $type
     */
    private function getProperty(array $type): string
    {
        $params = ArrayPropertyUtil::getProperty($type, 'params');

        // Prefer positional second parameter if provided
        $second = ArrayPropertyUtil::getProperty($params, 1);
        if (is_string($second) && $second !== '') {
            return $second;
        }

        // Fallback to previous named-style inside first param
        $first = ArrayPropertyUtil::getProperty($params, 0);
        if (is_array($first)) {
            $named = ArrayPropertyUtil::getProperty($first, 'property');
            if (is_string($named) && $named !== '') {
                return $named;
            }
        }

        return 'id';
    }

    /**
     * @param array<string, mixed> $type
     *
     * @return class-string|null
     */
    private function getClass(array $type): ?string
    {
        $params = ArrayPropertyUtil::getProperty($type, 'params');
        $first = ArrayPropertyUtil::getProperty($params, 0);

        if (is_array($first)) {
            $name = ArrayPropertyUtil::getProperty($first, 'name');

            return is_string($name) ? $name : null;
        }

        return is_string($first) ? $first : null;
    }

    /**
     * @param EntityRepository<object>|ObjectRepository<object> $repository
     */
    private function getObject(
        EntityRepository|ObjectRepository $repository,
        string $id,
        string $property,
    ): object {
        try {
            $object = $repository->findOneBy([$property => $id]);
        } catch (\Exception) {
            return new NullEntity($id);
        }

        if (!$object) {
            return new NullEntity($id);
        }

        return $object;
    }

    /**
     * @param array<string, mixed> $type
     */
    public function deserialize(JsonDeserializationVisitor $visitor, mixed $id, array $type): ?object
    {
        if (!$id) {
            return null;
        }

        $property = $this->getProperty($type);

        if (is_array($id)) {
            $id = ArrayPropertyUtil::getProperty($id, $property);
            if (!$id) {
                return null;
            }
        }

        $name = ArrayPropertyUtil::getProperty($type, 'name');
        if (!$name) {
            return null;
        }

        $entityClass = $this->getClass($type);
        if ($entityClass === null) {
            return null;
        }
        /** @var class-string $entityClass */
        $repository = $this->entityManager->getRepository($entityClass);

        return $this->getObject($repository, $id, $property);
    }
}
