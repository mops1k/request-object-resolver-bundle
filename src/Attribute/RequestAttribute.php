<?php

namespace RequestObjectResolverBundle\Attribute;

interface RequestAttribute
{
    /**
     * @return array<string, string>
     */
    public function getMap(): array;

    /**
     * @return array<string, mixed>
     */
    public function getSerializerContext(): array;
}
