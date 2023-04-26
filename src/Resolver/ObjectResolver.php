<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\Attribute\RequestAttribute;
use RequestObjectResolverBundle\Attribute\SkipValidation;
use RequestObjectResolverBundle\Attribute\ValidationGroups;
use RequestObjectResolverBundle\Chain\ChainManager;
use RequestObjectResolverBundle\Exceptions\ObjectValidationFailHttpException;
use RequestObjectResolverBundle\Exceptions\TypeDoesNotExists;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ObjectResolver implements ArgumentValueResolverInterface
{
    /**
     * @var iterable<class-string<RequestAttribute>>
     */
    protected iterable $attributes;

    /**
     * @param iterable<RequestAttribute> $attributes
     */
    public function __construct(
        protected ValidatorInterface $validator,
        protected ChainManager $chainManager,
        #[TaggedIterator('request_object.attributes')] iterable $attributes,
    ) {
        foreach ($attributes as $attribute) {
            if (!$attribute instanceof RequestAttribute) {
                continue;
            }

            $this->attributes[] = $attribute::class;
        }
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();
        if (null === $type) {
            return false;
        }

        if (!class_exists($type)) {
            return false;
        }

        $passedAttributes = $argument->getAttributes();
        foreach ($passedAttributes as $passedAttribute) {
            if (in_array($passedAttribute::class, $this->attributes, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return iterable<?object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();
        if (null === $type || !class_exists($type)) {
            throw new TypeDoesNotExists();
        }

        $object = $this->chainManager->resolve($request, $argument);
        if (null === $object) {
            return yield $object;
        }

        $skipValidationAttributes = $argument->getAttributes(SkipValidation::class, ArgumentMetadata::IS_INSTANCEOF);
        if (count($skipValidationAttributes) > 0) {
            return yield $object;
        }

        $validationGroups = [];
        /** @var array<ValidationGroups> $validationGroups */
        $validationGroupsAttributes = $argument->getAttributes(ValidationGroups::class, ArgumentMetadata::IS_INSTANCEOF);
        if (count($validationGroupsAttributes) > 0) {
            foreach ($validationGroupsAttributes as $validationGroupsAttribute) {
                $validationGroups = array_merge_recursive($validationGroups, $validationGroupsAttribute->getGroups());
            }
        }

        $this->validate($object, $validationGroups);

        return yield $object;
    }

    /**
     * @param array<string> $validationGroups
     */
    private function validate(object $object, array $validationGroups): void
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
            throw new ObjectValidationFailHttpException($constraints);
        }
    }
}
