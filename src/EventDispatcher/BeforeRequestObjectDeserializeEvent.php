<?php

namespace RequestObjectResolverBundle\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event;

class BeforeRequestObjectDeserializeEvent extends Event
{
    /**
     * @param array<mixed>  $resolvedParameters
     */
    public function __construct(
        private string $objectToResolve,
        private array $resolvedParameters = [],
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function getResolvedParameters(): array
    {
        return $this->resolvedParameters;
    }

    public function getObjectToResolve(): string
    {
        return $this->objectToResolve;
    }

    /**
     * @param mixed[] $resolvedParameters
     */
    public function setResolvedParameters(array $resolvedParameters): void
    {
        $this->resolvedParameters = $resolvedParameters;
    }
}
