<?php

namespace RequestObjectResolverBundle\Tests\Fixtures\Query;

use Symfony\Component\Validator\Constraints as Assert;

class TestQueryModel
{
    #[Assert\NotBlank]
    public string $test;

    #[Assert\Range(min: 1, max: 10)]
    public int $number;
}
