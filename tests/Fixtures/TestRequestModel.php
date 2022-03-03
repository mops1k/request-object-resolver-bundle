<?php

namespace RequestObjectResolverBundle\Tests\Fixtures;

use RequestObjectResolverBundle\RequestModelInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class TestRequestModel implements RequestModelInterface
{
    public string $test;

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
