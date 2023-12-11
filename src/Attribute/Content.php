<?php

namespace RequestObjectResolverBundle\Attribute;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class Content implements RequestAttribute
{
    /**
     * @param array<string, string> $map
     * @param array<string, mixed> $serializerContext
     */
    public function __construct(
        private array $map = [],
        private array $serializerContext = [],
        private string $format = JsonEncoder::FORMAT,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getMap(): array
    {
        return $this->map;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSerializerContext(): array
    {
        return $this->serializerContext;
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
