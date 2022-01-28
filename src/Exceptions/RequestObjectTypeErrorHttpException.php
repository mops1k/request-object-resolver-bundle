<?php

namespace Kvarta\RequestObjectResolverBundle\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestObjectTypeErrorHttpException extends BadRequestHttpException
{
    /**
     * @param array<mixed> $headers
     */
    public function __construct(private string $propertyPath, string $actualType, string $expectedType, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        $message = sprintf('Passed a value with type %s, expected type %s', $actualType, $expectedType);
        parent::__construct($message, $previous, $code, $headers);
    }

    public function getField(): string
    {
        return $this->propertyPath;
    }
}
