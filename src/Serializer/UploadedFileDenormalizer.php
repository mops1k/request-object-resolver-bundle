<?php

namespace RequestObjectResolverBundle\Serializer;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UploadedFileDenormalizer extends ObjectNormalizer
{
    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return false;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        if ($type === UploadedFile::class && $data instanceof UploadedFile) {
            return true;
        }

        return false;
    }

    /**
     * @param array<string, string> $context
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): ?UploadedFile
    {
        return $data;
    }
}
