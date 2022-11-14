<?php

declare(strict_types=1);

namespace RequestObjectResolverBundle\Tests\Fixtures;

use RequestObjectResolverBundle\ValidationGroupsInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TestRequestModelWithGroup implements ValidationGroupsInterface
{
    #[Assert\NotNull(groups: ['Test'])]
    public ?int $id = null;

    #[Assert\NotBlank]
    public string $test = '';

    /**
     * @return string[]|null
     */
    public static function validationGroups(): ?array
    {
        return ['Test'];
    }
}
