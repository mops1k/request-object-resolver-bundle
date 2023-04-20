<?php

namespace RequestObjectResolverBundle\Attribute;

use Symfony\Component\Serializer\Encoder\JsonEncoder;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class Content implements RequestAttribute
{
    /**
     * @param array<string, string> $map
     */
    public function __construct(private array $map = [], private string $format = JsonEncoder::FORMAT)
    {
    }

    /**
     * @return array<string, string>
     */
    public function getMap(): array
    {
        return $this->map;
    }

    public function getFormat(): string
    {
        return $this->format;
    }
}
