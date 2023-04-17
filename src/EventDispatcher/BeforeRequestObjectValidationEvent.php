<?php

declare(strict_types=1);

namespace RequestObjectResolverBundle\EventDispatcher;

use RequestObjectResolverBundle\RequestModelInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @deprecated
 */
class BeforeRequestObjectValidationEvent extends Event
{
    public function __construct(private RequestModelInterface $object)
    {
    }


    public function getObject(): RequestModelInterface
    {
        return $this->object;
    }
}
