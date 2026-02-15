<?php

declare(strict_types=1);

namespace Solitus0\JmsArgumentResolverBundle\Tests\Unit\ArgumentResolver;

use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Solitus0\JmsArgumentResolverBundle\ArgumentResolver\QueryParameterValueResolver;
use Solitus0\JmsArgumentResolverBundle\Event\PreValidateEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

require_once __DIR__ . '/DummyQueryPayload.php';

final class QueryParameterValueResolverTest extends TestCase
{
    public function testResolveReturnsEmptyWhenNoAttributes(): void
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $serializer->expects(self::never())->method('deserialize');
        $validator->expects(self::never())->method('validate');
        $dispatcher->expects(self::never())->method('dispatch');

        $resolver = new QueryParameterValueResolver($serializer, $validator, $dispatcher);
        $argument = new ArgumentMetadata('payload', DummyQueryPayload::class, false, false, null, false, []);

        $result = $resolver->resolve(Request::create('/'), $argument);

        self::assertSame([], $result);
    }

    public function testResolveUsesDefaultValidationGroupAndStoresErrors(): void
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $payload = new DummyQueryPayload();
        $request = Request::create('/?status=ok');
        $argument = new ArgumentMetadata(
            'payload',
            DummyQueryPayload::class,
            false,
            false,
            null,
            false,
            [new MapQueryString()]
        );

        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with(
                self::callback(static function (string $data): bool {
                    $decoded = json_decode($data, true);

                    return is_array($decoded) && $decoded['status'] === 'ok';
                }),
                DummyQueryPayload::class,
                'json'
            )
            ->willReturn($payload)
        ;

        $violations = new ConstraintViolationList();
        $validator
            ->expects(self::once())
            ->method('validate')
            ->with($payload, null, ['Default'])
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

        $resolver = new QueryParameterValueResolver($serializer, $validator, $dispatcher);

        $result = $resolver->resolve($request, $argument);

        self::assertSame([$payload], $result);
        self::assertSame($violations, $request->attributes->get('validationErrors'));
    }
}
