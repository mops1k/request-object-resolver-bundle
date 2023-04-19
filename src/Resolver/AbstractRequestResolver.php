<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\Exceptions\RequestObjectValidationFailHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractRequestResolver implements ArgumentValueResolverInterface
{
    protected ?string $attributeClass = null;

    public function __construct(
        protected SerializerInterface $serializer,
        protected ValidatorInterface $validator,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (!$this->attributeClass) {
            return false;
        }

        $type = $argument->getType();
        if (null === $type) {
            return false;
        }

        if (!class_exists($type)) {
            return false;
        }

        $contentAttributes = $argument->getAttributes($this->attributeClass, ArgumentMetadata::IS_INSTANCEOF);
        if (count($contentAttributes) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param array<string>|null $validationGroups
     */
    public function validate(object $object, ?array $validationGroups = null): void
    {
        $groups = new GroupSequence($validationGroups);
        if (count($validationGroups) === 0) {
            $groups = null;
        }
        $constraints = $this->validator->validate(
            value: $object,
            groups: $groups
        );

        if (count($constraints) > 0) {
            throw new RequestObjectValidationFailHttpException($constraints);
        }
    }
}
