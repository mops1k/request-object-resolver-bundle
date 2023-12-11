<?php

namespace RequestObjectResolverBundle\Chain;

use RequestObjectResolverBundle\Attribute\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class FormResolver extends AbstractResolver
{
    protected string $attributeClassName = Form::class;

    /**
     * @param array<string, string> $options
     */
    public function resolve(
        Request $request,
        ArgumentMetadata $metadata,
        ?object $object = null,
        array $options = [],
    ): ?object {
        $this->data = \array_merge_recursive($request->request->all(), $request->files->all());

        return parent::resolve($request, $metadata, $object, $options);
    }
}
