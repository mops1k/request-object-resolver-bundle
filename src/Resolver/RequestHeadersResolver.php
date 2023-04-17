<?php

namespace RequestObjectResolverBundle\Resolver;

use RequestObjectResolverBundle\Exceptions\RequestHeadersValidationFailHttpException;
use RequestObjectResolverBundle\Helper\RequestNormalizeHelper;
use RequestObjectResolverBundle\Http\RequestHeaders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @deprecated
 */
final class RequestHeadersResolver implements ArgumentValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

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

        $result = new $type(RequestNormalizeHelper::normalizeHeaders($request));

        $constraints = $this->validator->validate($result);
        if (count($constraints) > 0) {
            throw new RequestHeadersValidationFailHttpException($constraints);
        }

        yield $result;
    }
}
