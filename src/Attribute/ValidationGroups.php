<?php

namespace RequestObjectResolverBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class ValidationGroups
{
    /**
     * @param array<string>|null $groups
     */
    public function __construct(private ?array $groups = null)
    {
    }

    /**
     * @return array<string>|null
     */
    public function getGroups(): ?array
    {
        return $this->groups;
    }
}
