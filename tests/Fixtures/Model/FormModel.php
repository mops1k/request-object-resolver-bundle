<?php

namespace RequestObjectResolverBundle\Tests\Fixtures\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

final class FormModel
{
    #[NotBlank]
    #[GreaterThan(0)]
    #[LessThan(200)]
    public int $id;

    public UploadedFile $file;

    /**
     * @var array<UploadedFile>
     */
    public array $files = [];
}
