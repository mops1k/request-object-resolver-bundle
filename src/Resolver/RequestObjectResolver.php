<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent;
use RequestObjectResolverBundle\Exceptions\RequestObjectValidationFailHttpException;
use RequestObjectResolverBundle\Helper\RequestNormalizeHelper;
use RequestObjectResolverBundle\Interfaces\RequestObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class RequestObjectResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return \is_a($argument->getType(), RequestObjectInterface::class, true);
    }

    /**
     * @return \Generator<RequestObjectInterface>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        // т.к. это библиотека, то единственный способ безболезненно повлиять на десериализацию данных в объект - это создать событие до того, как она будет запущена
        $event = new BeforeRequestObjectDeserializeEvent($type, RequestNormalizeHelper::normalizeRequest($request));
        $this->eventDispatcher->dispatch($event, BeforeRequestObjectDeserializeEvent::class);
        $parameters = $event->getResolvedParameters();

        // десериализуем пришедший и обработанный запрос в объект
        $object = $this->serializer->deserialize(
            \json_encode($parameters, JSON_THROW_ON_ERROR),
            $type,
            'json',
            ['disable_type_enforcement' => true],
        );

        RequestNormalizeHelper::addFilesFromRequestToObject($request, $object);

        // проводим валидацию объекта
        $constraints = $this->validator->validate($object);
        if (count($constraints) > 0) {
            throw new RequestObjectValidationFailHttpException($constraints, headers: $request->headers->all());
        }

        yield $object;
    }
}
