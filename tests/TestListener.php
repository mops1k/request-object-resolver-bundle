<?php

namespace RequestObjectResolverBundle\Tests;

use RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent;

class TestListener
{
    public function beforeDeserialization(BeforeRequestObjectDeserializeEvent $event): void
    {
        if (!is_a($event->getObjectToResolve(), TestRequestObject::class, true)) {
            return;
        }

        $parameters = $event->getResolvedParameters();
        $parameters['test'] = 'test_value_modified';
        $event->setResolvedParameters($parameters);
    }
}
