<?php

namespace RequestObjectResolverBundle\Serializer;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UploadedFileDenormalizer implements DenormalizerInterface
{
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

    /**
     * @return array<string, bool>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            UploadedFile::class => false,
        ];
    }
}
