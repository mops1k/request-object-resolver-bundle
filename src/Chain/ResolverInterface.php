<?php

namespace RequestObjectResolverBundle\Chain;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

interface ResolverInterface
{
    /**
     * @param array<string, string> $options
     */
    public function resolve(
        Request $request,
        ArgumentMetadata $metadata,
        ?object $object = null,
        array $options = [],
    ): ?object;

    public function supports(ArgumentMetadata $argumentMetadata): bool;
}
