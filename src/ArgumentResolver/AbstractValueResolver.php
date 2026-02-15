<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\ArgumentResolver;

use JMS\Serializer\SerializerInterface;
use Solitus0\JmsArgumentResolverBundle\Event\PreValidateEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractValueResolver implements ValueResolverInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @return iterable<object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attributeClasses = [
            MapQueryString::class,
            MapRequestPayload::class,
            ValueResolver::class,
        ];

        $attribute = null;
        foreach ($attributeClasses as $class) {
            $attributes = $argument->getAttributesOfType($class, ArgumentMetadata::IS_INSTANCEOF);
            if ($attributes !== []) {
                $attribute = $attributes[0];

                break;
            }
        }

        if (!$attribute) {
            return [];
        }

        $data = $this->getData($request);

        $object = $this->serializer->deserialize($data, $argument->getType(), 'json');

        $groups = [];
        if ($attribute instanceof MapQueryString || $attribute instanceof MapRequestPayload) {
            $groups = $attribute->validationGroups ?? [];
        }
        if (empty($groups)) {
            $groups = ['Default'];
        }

        $this->eventDispatcher->dispatch(new PreValidateEvent($object));

        if ($object instanceof ResolvableValidationGroupsInterface) {
            $groups = array_merge($groups, $object->resolveValidationGroups());
        }

        $errors = $this->validator->validate($object, null, $groups);
        $request->attributes->set('validationErrors', $errors);

        return [$object];
    }

    abstract protected function getData(Request $request): string;
}
