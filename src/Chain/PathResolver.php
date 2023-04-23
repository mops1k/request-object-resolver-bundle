<?php

namespace RequestObjectResolverBundle\Chain;

use RequestObjectResolverBundle\Attribute\Path;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class PathResolver extends AbstractResolver
{
    protected string $attributeClassName = Path::class;

    public function resolve(Request $request, ArgumentMetadata $metadata, ?object $object = null): ?object
    {
        $this->data = $request->attributes->get('_route_params', []);

        return parent::resolve($request, $metadata, $object);
    }
}
