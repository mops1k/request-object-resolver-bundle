<?php

namespace RequestObjectResolverBundle\Tests;

use RequestObjectResolverBundle\Interfaces\RequestObjectInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class TestRequestObject implements RequestObjectInterface
{
    #[NotNull]
    #[NotBlank]
    public ?string $test = null;

    /**
     * @SerializedName(serializedName="test_json")
     */
    public ?string $testJson = null;

    #[NotNull]
    #[NotBlank]
    /**
     * @SerializedName(serializedName="test_query")
     */
    public ?string $testQuery = null;

    /**
     * @var array<UploadedFile>
     */
    public array $testFile = [];
}
