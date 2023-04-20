<?php

namespace RequestObjectResolverBundle\Chain;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

interface ResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $metadata, ?object $object = null): ?object;

    public function supports(ArgumentMetadata $argumentMetadata): bool;
}
