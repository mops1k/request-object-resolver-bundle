<?php

namespace RequestObjectResolverBundle\Attribute;

interface RequestAttribute
{
    public function getMap(): array;

    public function getSerializerContext(): array;
}
