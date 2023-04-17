<?php

namespace RequestObjectResolverBundle\Http;

/**
 * @deprecated
 */
class RequestCookies
{
    /**
     * @param array<mixed> $cookies
     */
    public function __construct(protected array $cookies = [])
    {
    }

    public function get(string $key): ?string
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->cookies[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->cookies[$key]);
    }

    /**
     * @return array<array{key: string, value: string}>
     */
    public function all(): array
    {
        $result = [];
        foreach ($this->cookies as $key => $value) {
            $result[] = [
                'key' => $key,
                'value' => $value,
            ];
        }

        return $result;
    }
}
