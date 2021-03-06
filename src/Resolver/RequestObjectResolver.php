<?php

namespace RequestObjectResolverBundle\Resolver;

use Generator;
use RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent;
use RequestObjectResolverBundle\Exceptions\RequestObjectDeserializationHttpException;
use RequestObjectResolverBundle\Exceptions\RequestObjectTypeErrorHttpException;
use RequestObjectResolverBundle\Exceptions\RequestObjectValidationFailHttpException;
use RequestObjectResolverBundle\Helper\RequestNormalizeHelper;
use RequestObjectResolverBundle\RequestModelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use TypeError;

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
        return is_a($argument->getType(), RequestModelInterface::class, true);
    }

    /**
     * @return Generator<RequestModelInterface>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        $type = $argument->getType();

        // т.к. это библиотека, то единственный способ безболезненно повлиять на десериализацию данных в объект - это создать событие до того, как она будет запущена
        $event = new BeforeRequestObjectDeserializeEvent($type, RequestNormalizeHelper::normalizeRequest($request));
        $this->eventDispatcher->dispatch($event, BeforeRequestObjectDeserializeEvent::class);
        $parameters = $event->getResolvedParameters();

        // десериализуем пришедший и обработанный запрос в объект
        try {
            $object = $this->serializer->deserialize(
                json_encode($parameters, JSON_THROW_ON_ERROR),
                $type,
                'json',
                [
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                    DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
                ],
            );

            RequestNormalizeHelper::addFilesFromRequestToObject($request, $object);

            // проводим валидацию объекта
            $constraints = $this->validator->validate($object);
            if (count($constraints) > 0) {
                throw new RequestObjectValidationFailHttpException($constraints);
            }

            yield $object;
        } catch (TypeError $error) {
            if (preg_match(
                '/^Cannot assign (\S+) to property \S+::\$(\S+) of type (\S+)$/',
                $error->getMessage(),
                $matches
            )) {
                // $propertyPath может быть не точным (из-за SerializedName), но больше у нас ничего нет
                [, $actualType, $propertyPath, $expectedType] = $matches;

                throw new RequestObjectTypeErrorHttpException($propertyPath, $actualType, $expectedType);
            }

            throw $error;
        } catch (PartialDenormalizationException $exception) {
            $errors = [];
            foreach ($exception->getErrors() as $error) {
                $errors[] = (string)$error;
            }

            throw new RequestObjectDeserializationHttpException($errors);
        }
    }
}
