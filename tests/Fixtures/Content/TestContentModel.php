<?php

namespace RequestObjectResolverBundle\Tests\Fixtures\Content;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class TestContentModel
{
    #[Assert\NotBlank]
    public string $test;
    #[SerializedName('test_bool')]
    public bool $testBool;
}
