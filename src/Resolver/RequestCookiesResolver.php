<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\Http\RequestCookies;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @deprecated
 */
final class RequestCookiesResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_a($argument->getType(), RequestCookies::class, true);
    }

    /**
     * @return \Generator<RequestCookies>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        $type = $argument->getType();

        yield new $type($request->cookies->all());
    }
}
