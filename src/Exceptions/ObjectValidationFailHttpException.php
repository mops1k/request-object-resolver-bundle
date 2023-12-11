<?php

namespace RequestObjectResolverBundle\Exceptions;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ObjectValidationFailHttpException extends BadRequestHttpException
{
    /**
     * @var array<array{field: string, message: string}>
     */
    protected array $errors = [];

    /**
     * @var ConstraintViolationListInterface<ConstraintViolationInterface>
     */
    protected ConstraintViolationListInterface $constraints;

    /**
     * @param array<mixed> $headers
     */
    public function __construct(ConstraintViolationListInterface $constraints, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        $errorMessages = [];
        $this->constraints = $constraints;
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
     * @return array<array{field: string, message: string}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return ConstraintViolationListInterface<ConstraintViolationInterface>
     */
    public function getConstraints(): ConstraintViolationListInterface
    {
        return $this->constraints;
    }
}
