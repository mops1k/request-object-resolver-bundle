<?php

namespace RequestObjectResolverBundle\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestObjectDeserializationHttpException extends BadRequestHttpException
{
    /**
     * @param array<string> $errors
     * @param array<mixed> $headers
     */
    public function __construct(private array $errors, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct('Request object deserialization error', $previous, $code, $headers);
    }

    /**
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
