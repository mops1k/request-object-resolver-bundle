<?php

namespace RequestObjectResolverBundle\Tests\Fixtures\Model;

use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

final class QueryModel
{
    #[NotBlank]
    #[GreaterThanOrEqual(1)]
    public int $id;
}
