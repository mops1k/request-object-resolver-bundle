<?php

namespace RequestObjectResolverBundle\Chain;

use RequestObjectResolverBundle\Attribute\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
final class QueryResolver extends AbstractResolver
{
    protected string $attributeClassName = Query::class;

    public function resolve(Request $request, ArgumentMetadata $metadata, ?object $object = null): ?object
    {
        $this->data = $request->query->all();

        return parent::resolve($request, $metadata, $object);
    }
}
