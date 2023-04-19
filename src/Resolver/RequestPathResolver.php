<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\Attribute\Path;
use RequestObjectResolverBundle\Attribute\SkipValidation;
use RequestObjectResolverBundle\Attribute\ValidationGroups;
use RequestObjectResolverBundle\Exceptions\ObjectDeserializationHttpException;
use RequestObjectResolverBundle\Exceptions\SerializerNotFound;
use RequestObjectResolverBundle\Exceptions\TypeDoesNotExists;
use RequestObjectResolverBundle\Exceptions\TypeErrorHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final class RequestPathResolver extends AbstractRequestResolver
{
    protected ?string $attributeClass = Path::class;

    /**
     * @return iterable<object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();
        if (null === $type || !class_exists($type)) {
            throw new TypeDoesNotExists();
        }

        $data = $request->attributes->get('_route_params', []);

        if (!$this->serializer instanceof Serializer) {
            throw new SerializerNotFound();
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

                throw new TypeErrorHttpException($propertyPath, $actualType, $expectedType);
            }

            throw $error;
        } catch (PartialDenormalizationException $exception) {
            $errors = [];
            foreach ($exception->getErrors() as $error) {
                $errors[] = (string)$error->getMessage();
            }

            throw new ObjectDeserializationHttpException($errors, $exception);
        }

        if ($skipValidation) {
            return [$object];
        }

        $this->validate($object, $validationGroups);

        return [$object];
    }
}
