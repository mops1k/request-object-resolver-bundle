<?php

namespace Kvarta\RequestObjectResolverBundle\Tests\Fixtures;

use Kvarta\RequestObjectResolverBundle\Http\RequestHeaders;
use Symfony\Component\Validator\Constraints\NotBlank;

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
