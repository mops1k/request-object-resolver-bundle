<?php

namespace RequestObjectResolverBundle\Tests\Fixtures\Model;

use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PathModel
{
    #[NotBlank]
    #[GreaterThan(0)]
    #[LessThan(200)]
    public int $id;
}
