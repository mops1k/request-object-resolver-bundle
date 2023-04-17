<?php

namespace RequestObjectResolverBundle\Tests\Fixtures;

use RequestObjectResolverBundle\Http\RequestHeaders;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @deprecated
 */
class TestRequestHeaders extends RequestHeaders
{
    #[NotBlank]
    private string $test;

    public function __construct(array $headers)
    {
        parent::__construct($headers);

        $this->test = $headers['test'] ?? '';
    }

    public function getTest(): string
    {
        return $this->test;
    }
}
