<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\Exceptions\RequestObjectValidationFailHttpException;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractRequestResolver implements ValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
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
