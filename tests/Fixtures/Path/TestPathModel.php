<?php

namespace RequestObjectResolverBundle\Tests\Fixtures\Path;

use Symfony\Component\Validator\Constraints as Assert;

class TestPathModel
{
    #[Assert\Range(min: 1)]
    public int $object;
}
