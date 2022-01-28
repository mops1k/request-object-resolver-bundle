<?php

namespace RequestObjectResolverBundle\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestObjectValidationFailHttpException extends BadRequestHttpException
{
    /**
     * @var array<string, string>
     */
    private array $errors = [];

    /**
     * @param array<mixed> $headers
     */
    public function __construct(ConstraintViolationListInterface $constraints, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        $errorMessages = [];
        /** @var ConstraintViolationInterface $constraint */
        foreach ($constraints as $constraint) {
            $this->errors[] = [
                'field' => $constraint->getPropertyPath(),
                'message' => $constraint->getMessage(),
            ];
            $errorMessages[] = \sprintf(
                '[%s] %s',
                $constraint->getPropertyPath(),
                $constraint->getMessage(),
            );
        }

        $message = \sprintf('Request validation failed. Errors: %s', implode(', ', $errorMessages));
        parent::__construct($message, $previous, $code, $headers);
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
