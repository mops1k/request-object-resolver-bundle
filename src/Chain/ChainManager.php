<?php

namespace RequestObjectResolverBundle\Chain;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ChainManager
{
    /**
     * @var iterable<ResolverInterface>
     */
    private iterable $resolvers = [];

    /**
     * @param iterable<ResolverInterface> $resolvers
     */
    public function __construct(
        #[TaggedIterator('request_object.resolver', defaultPriorityMethod: 'getPriority')] iterable $resolvers,
    ) {
        foreach ($resolvers as $resolver) {
            if (!$resolver instanceof ResolverInterface) {
                continue;
            }

            $this->resolvers[] = $resolver;
        }
    }

    public function resolve(Request $request, ArgumentMetadata $argumentMetadata): ?object
    {
        $object = null;
        foreach ($this->resolvers as $resolver) {
            if (!$resolver->supports($argumentMetadata)) {
                continue;
            }

            $object = $resolver->resolve($request, $argumentMetadata, $object);
        }

        return $object;
    }
}
