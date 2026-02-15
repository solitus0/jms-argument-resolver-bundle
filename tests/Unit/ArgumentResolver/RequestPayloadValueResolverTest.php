<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Tests\Unit\ArgumentResolver;

use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Solitus0\JmsArgumentResolverBundle\ArgumentResolver\RequestPayloadValueResolver;
use Solitus0\JmsArgumentResolverBundle\Event\PreValidateEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

require_once __DIR__ . '/DummyPayload.php';
require_once __DIR__ . '/DummyResolvablePayload.php';

final class RequestPayloadValueResolverTest extends TestCase
{
    public function testResolveUsesCustomAndResolvableValidationGroups(): void
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $payload = new DummyResolvablePayload();
        $request = Request::create('/', 'POST', server: ['CONTENT_TYPE' => 'application/json']);
        $argument = new ArgumentMetadata(
            'payload',
            DummyResolvablePayload::class,
            false,
            false,
            null,
            false,
            [new MapRequestPayload(validationGroups: ['Api'])]
        );

        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with('{}', DummyResolvablePayload::class, 'json')
            ->willReturn($payload)
        ;

        $violations = new ConstraintViolationList();
        $validator
            ->expects(self::once())
            ->method('validate')
            ->with($payload, null, ['Api', 'Extra'])
            ->willReturn($violations)
        ;

        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(static function (PreValidateEvent $event) use ($payload): bool {
                return $event->object === $payload;
            }))
            ->willReturnArgument(0)
        ;

        $resolver = new RequestPayloadValueResolver($serializer, $validator, $dispatcher);

        $result = $resolver->resolve($request, $argument);

        self::assertSame([$payload], $result);
        self::assertSame($violations, $request->attributes->get('validationErrors'));
    }

    public function testResolveUsesEmptyObjectForEmptyJsonContent(): void
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $payload = new DummyPayload();
        $request = Request::create('/', 'POST', server: ['CONTENT_TYPE' => 'application/json']);
        $argument = new ArgumentMetadata(
            'payload',
            DummyPayload::class,
            false,
            false,
            null,
            false,
            [new MapRequestPayload()]
        );

        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with('{}', DummyPayload::class, 'json')
            ->willReturn($payload)
        ;

        $validator
            ->expects(self::once())
            ->method('validate')
            ->with($payload, null, ['Default'])
            ->willReturn(new ConstraintViolationList())
        ;

        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(PreValidateEvent::class))
            ->willReturnArgument(0)
        ;

        $resolver = new RequestPayloadValueResolver($serializer, $validator, $dispatcher);

        $resolver->resolve($request, $argument);
    }

    public function testResolveUsesFormPayloadWhenNotJson(): void
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $payload = new DummyPayload();
        $tempFile = tempnam(sys_get_temp_dir(), 'upload');
        self::assertIsString($tempFile);
        file_put_contents($tempFile, 'content');
        $uploadedFile = new \Symfony\Component\HttpFoundation\File\UploadedFile($tempFile, 'file.txt', null, null, true);

        $request = Request::create('/', 'POST', ['name' => 'Tess'], [], ['file' => $uploadedFile]);
        $argument = new ArgumentMetadata(
            'payload',
            DummyPayload::class,
            false,
            false,
            null,
            false,
            [new MapRequestPayload()]
        );

        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with(
                self::callback(static function (string $data): bool {
                    $decoded = json_decode($data, true);

                    return is_array($decoded)
                        && $decoded['name'] === 'Tess'
                        && array_key_exists('file', $decoded);
                }),
                DummyPayload::class,
                'json'
            )
            ->willReturn($payload)
        ;

        $validator
            ->expects(self::once())
            ->method('validate')
            ->with($payload, null, ['Default'])
            ->willReturn(new ConstraintViolationList())
        ;

        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(PreValidateEvent::class))
            ->willReturnArgument(0)
        ;

        $resolver = new RequestPayloadValueResolver($serializer, $validator, $dispatcher);

        $resolver->resolve($request, $argument);
    }
}
