<?php

namespace RequestObjectResolverBundle\Http;

/**
 * @template THeaders
 * @todo для чего THeaders? Не используется
 */
class RequestHeaders
{
    /**
     * @param array<mixed> $headers
     */
    public function __construct(protected array $headers)
    {
    }

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->headers[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->headers[$key]);
    }
}
