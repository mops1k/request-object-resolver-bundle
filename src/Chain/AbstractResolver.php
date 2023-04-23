<?php

namespace RequestObjectResolverBundle\Chain;

use RequestObjectResolverBundle\Attribute\RequestAttribute;
use RequestObjectResolverBundle\Exceptions\ObjectDeserializationHttpException;
use RequestObjectResolverBundle\Exceptions\SerializerNotFound;
use RequestObjectResolverBundle\Exceptions\TypeErrorHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractResolver implements ResolverInterface
{
    protected array $defaultContext = [
        AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
    ];

    protected array $data = [];

    /**
     * @var class-string<RequestAttribute>
     */
    protected string $attributeClassName;

    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $metadata, ?object $object = null): ?object
    {
        if (!$this->serializer instanceof Serializer) {
            throw new SerializerNotFound();
        }

        $type = $metadata->getType();
        $data = $this->data;
        $fieldsMapping = [];

        $attributes = $metadata->getAttributes();
        $context = [];
        foreach ($attributes as $attribute) {
            if ($attribute instanceof $this->attributeClassName) {
                /** @var RequestAttribute $attribute */
                $fieldsMapping = array_merge_recursive($fieldsMapping, $attribute->getMap());
                $context = array_merge_recursive($this->defaultContext, $attribute->getSerializerContext());
            }
        }

        if (count($fieldsMapping) > 0) {
            foreach ($data as $name => $value) {
                if (array_key_exists($name, $fieldsMapping)) {
                    $data[$fieldsMapping[$name]] = $value;
                    unset($data[$name]);
                }
            }
        }

        if (null !== $object) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $object;
        }

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

        return $object;
    }

    public function supports(ArgumentMetadata $argumentMetadata): bool
    {
        $attributes = $argumentMetadata->getAttributes($this->attributeClassName, ArgumentMetadata::IS_INSTANCEOF);

        return count($attributes) > 0;
    }
}
