<?php

namespace RequestObjectResolverBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class Query implements RequestAttribute
{
    /**
     * @param array<string, string> $map
     */
    public function __construct(private array $map = [], private array $serializerContext = [])
    {
    }

    /**
     * @return array<string, string>
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * @return array<string, string>
     */
    public function getSerializerContext(): array
    {
        return $this->serializerContext;
    }
}
