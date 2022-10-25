<?php

declare(strict_types=1);

namespace RequestObjectResolverBundle\EventDispatcher;

use RequestObjectResolverBundle\RequestModelInterface;
use Symfony\Contracts\EventDispatcher\Event;

class BeforeRequestObjectValidationEvent extends Event
{
    public function __construct(private RequestModelInterface $object)
    {
    }

    /**
     * @return RequestModelInterface
     */
    public function getObject(): RequestModelInterface
    {
        return $this->object;
    }
}