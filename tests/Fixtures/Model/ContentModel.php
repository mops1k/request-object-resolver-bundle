<?php

namespace RequestObjectResolverBundle\Tests\Fixtures\Model;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ContentModel
{
    #[NotBlank]
    public int $id;

    #[SerializedName('name')]
    public ?string $userName = null;
}
