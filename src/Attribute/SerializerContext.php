<?php

namespace RequestObjectResolverBundle\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class SerializerContext
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(private array $context = [])
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
