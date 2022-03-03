<?php

namespace RequestObjectResolverBundle\EventDispatcher;

use RequestObjectResolverBundle\RequestModelInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeRequestObjectDeserializeEvent extends Event
{
    /**
     * @param class-string<\Kvarta\RequestObjectResolverBundle\RequestModelInterface> $objectToResolve
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
     * @return class-string<RequestModelInterface>
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
