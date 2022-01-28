<?php

namespace RequestObjectResolverBundle\EventDispatcher;

use RequestObjectResolverBundle\Interfaces\RequestObjectInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeRequestObjectDeserializeEvent extends Event
{
    /**
     * @param class-string<RequestObjectInterface> $objectToResolve
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

    /**
     * @return class-string<RequestObjectInterface>
     */
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
