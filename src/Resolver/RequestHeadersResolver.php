<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\Helper\RequestNormalizeHelper;
use RequestObjectResolverBundle\Http\RequestHeaders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class RequestHeadersResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_a($argument->getType(), RequestHeaders::class, true);
    }

    /**
     * @return \Generator<RequestHeaders>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        $type = $argument->getType();

        yield new $type(RequestNormalizeHelper::normalizeHeaders($request));
    }
}
