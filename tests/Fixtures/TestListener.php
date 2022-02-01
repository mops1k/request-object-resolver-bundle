<?php

namespace Kvarta\RequestObjectResolverBundle\Tests\Fixtures;

use Kvarta\RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent;

class TestListener
{
    public function beforeDeserialization(BeforeRequestObjectDeserializeEvent $event): void
    {
        if (!is_a($event->getObjectToResolve(), TestRequestModel::class, true)) {
            return;
        }

        $parameters = $event->getResolvedParameters();
        $parameters['test'] = 'test_value_modified';
        $event->setResolvedParameters($parameters);
    }
}
