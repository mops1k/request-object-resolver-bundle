<?php

namespace RequestObjectResolverBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class Path implements RequestAttribute
{
    /**
     * @param array<string, string> $map
     */
    public function __construct(private array $map = [])
    {
    }

    /**
     * @return array<string, string>
     */
    public function getMap(): array
    {
        return $this->map;
    }
}
