<?php

namespace RequestObjectResolverBundle\Chain;

use RequestObjectResolverBundle\Attribute\Content;
use RequestObjectResolverBundle\Exceptions\ObjectDeserializationHttpException;
use RequestObjectResolverBundle\Exceptions\TypeErrorHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class ContentResolver implements ResolverInterface
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $metadata, ?object $object = null): ?object
    {
        $type = $metadata->getType();
        $format = null;
        $fieldsMapping = [];

        $attributes = $metadata->getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Content) {
                $fieldsMapping = array_merge_recursive($fieldsMapping, $attribute->getMap());
                if (null === $format) {
                    $format = $attribute->getFormat();
                }
            }
        }

        $context = [
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
            DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
        ];

        $content = $request->getContent();
        if (count($fieldsMapping) > 0 && $this->serializer instanceof Serializer) {
            $decoded = $this->serializer->decode($content, $format, $context);
            if (is_array($decoded)) {
                foreach ($decoded as $name => $value) {
                    if (!\array_key_exists($name, $fieldsMapping)) {
                        continue;
                    }

                    $decoded[$fieldsMapping[$name]] = $value;
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

        return $object;
    }

    public function supports(ArgumentMetadata $argumentMetadata): bool
    {
        $attributes = $argumentMetadata->getAttributes(Content::class, ArgumentMetadata::IS_INSTANCEOF);

        return count($attributes) > 0;
    }
}
