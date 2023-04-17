<?php

namespace RequestObjectResolverBundle\Http;

/**
 * @deprecated
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
