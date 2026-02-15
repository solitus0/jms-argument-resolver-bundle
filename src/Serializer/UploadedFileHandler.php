<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Serializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UploadedFileHandler implements SubscribingHandlerInterface
{
    public function __construct(
        private readonly RequestStack $requestStack
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
                'type' => 'UploadedFile',
                'method' => 'deserialize',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $type
     */
    public function deserialize(
        JsonDeserializationVisitor $visitor,
        mixed $data,
        array $type,
        Context $context,
    ): mixed {

        $request = $this->requestStack->getCurrentRequest();
        if (!$request instanceof Request) {
            return null;
        }

        $propertyMetadata = $context->getMetadataStack()[0];
        $propertyName = $propertyMetadata->name;
        if (!$propertyName) {
            return null;
        }

        return $request->files->get($propertyName);

    }
}
