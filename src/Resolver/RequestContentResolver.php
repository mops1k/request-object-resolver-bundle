<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\Attribute\Content;
use RequestObjectResolverBundle\Attribute\SkipValidation;
use RequestObjectResolverBundle\Attribute\ValidationGroups;
use RequestObjectResolverBundle\Exceptions\ObjectDeserializationHttpException;
use RequestObjectResolverBundle\Exceptions\TypeDoesNotExists;
use RequestObjectResolverBundle\Exceptions\TypeErrorHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestContentResolver extends AbstractRequestResolver
{
    protected ?string $attributeClass = Content::class;

    /**
     * @return iterable<object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();
        if (null === $type || !class_exists($type)) {
            throw new TypeDoesNotExists();
        }

        $contentAttributes = $argument->getAttributes($this->attributeClass, ArgumentMetadata::IS_INSTANCEOF);
        if (count($contentAttributes) === 0) {
            return [null];
        }

        $format = null;
        $contentFieldsMapping = [];
        $validationGroups = [];
        $skipValidation = false;

        $attributes = $argument->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Content) {
                $contentFieldsMapping = array_merge_recursive($contentFieldsMapping, $attribute->getMap());
                if (null === $format) {
                    $format = $attribute->getFormat();
                }

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

        $context = [
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
        ];

        $content = $request->getContent();
        if (count($contentFieldsMapping) > 0 && $this->serializer instanceof Serializer) {
            $decoded = $this->serializer->decode($content, $format, $context);
            if (is_array($decoded)) {
                foreach ($decoded as $name => $value) {
                    if (!\array_key_exists($name, $contentFieldsMapping)) {
                        continue;
                    }

                    $decoded[$contentFieldsMapping[$name]] = $value;
                    unset($decoded[$name]);
                }
            }

            $content = $this->serializer->encode($decoded, $format, $context);
        }

        try {
            $object = $this->serializer->deserialize($content, $type, $format, $context);
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
