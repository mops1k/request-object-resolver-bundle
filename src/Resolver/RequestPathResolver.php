<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\Attribute\Path;
use RequestObjectResolverBundle\Attribute\SkipValidation;
use RequestObjectResolverBundle\Attribute\ValidationGroups;
use RequestObjectResolverBundle\Exceptions\RequestObjectDeserializationHttpException;
use RequestObjectResolverBundle\Exceptions\RequestObjectTypeErrorHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestPathResolver extends AbstractRequestResolver
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
        parent::__construct($this->validator);
    }

    /**
     * @return iterable<object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $data = $request->attributes->get('_route_params', []);
        if (count($data) === 0) {
            return [];
        }

        $type = $argument->getType();
        if (null === $type) {
            return [];
        }

        if (!class_exists($type)) {
            return [];
        }

        $pathAttributes = $argument->getAttributesOfType(Path::class, ArgumentMetadata::IS_INSTANCEOF);
        if (count($pathAttributes) === 0) {
            return [];
        }

        if (!$this->serializer instanceof Serializer) {
            return [];
        }

        $pathFieldsMapping = [];
        $validationGroups = [];
        $skipValidation = false;

        $attributes = $argument->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Path) {
                $pathFieldsMapping = array_merge_recursive($pathFieldsMapping, $attribute->getMap());

                continue;
            }

            if ($attribute instanceof ValidationGroups) {
                if (null === $attribute->getGroups()) {
                    continue;
                }
                $validationGroups = array_merge_recursive($validationGroups, $attribute->getGroups());

                continue;
            }

            if ($attribute instanceof SkipValidation) {
                $skipValidation = true;
            }
        }

        if (count($pathFieldsMapping) > 0) {
            foreach ($data as $name => $value) {
                if (array_key_exists($name, $pathFieldsMapping)) {
                    $data[$pathFieldsMapping[$name]] = $value;
                    unset($data[$name]);
                }
            }
        }

        $context = [
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
        ];

        try {
            $object = $this->serializer->denormalize(
                data: $data,
                type: $type,
                context: $context
            );
        } catch (\TypeError $error) {
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
                $errors[] = (string)$error->getMessage();
            }

            throw new RequestObjectDeserializationHttpException($errors, $exception);
        }

        if ($skipValidation) {
            return [$object];
        }

        $this->validate($object, $validationGroups);

        return [$object];
    }
}
