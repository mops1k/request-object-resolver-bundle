<?php

namespace RequestObjectResolverBundle\Tests\Fixtures\Model;

use Symfony\Component\Validator\Constraints\NotBlank;

final class UnionModel
{
    #[NotBlank]
    public int $id;

    #[NotBlank]
    public string $username;
}
