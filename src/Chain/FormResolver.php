<?php

namespace RequestObjectResolverBundle\Chain;

use RequestObjectResolverBundle\Attribute\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class FormResolver extends AbstractResolver
{
    protected string $attributeClassName = Form::class;

    public function resolve(Request $request, ArgumentMetadata $metadata, ?object $object = null): ?object
    {
        $this->data = $request->request->all();

        return parent::resolve($request, $metadata, $object);
    }
}
