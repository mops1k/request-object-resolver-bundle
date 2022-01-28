<?php

namespace Kvarta\RequestObjectResolverBundle\Helper;

use Doctrine\Inflector\InflectorFactory;
use Kvarta\RequestObjectResolverBundle\Interfaces\RequestObjectInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class RequestNormalizeHelper
{
    private function __construct()
    {
    }

    /**
     * @return array<mixed>
     */
    public static function normalizeRequest(Request $request): array
    {
        $queryParameters = $request->query->all();
        $requestParameters = $request->request->all();

        try {
            $contentData = \json_decode($request->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $contentData = ['rawContent' => $request->getContent()];
        }

        $routeParams = $request->attributes->get('_route_params', []);

        return \array_merge($queryParameters, $requestParameters, $contentData, $routeParams);
    }

    public static function addFilesFromRequestToObject(Request $request, RequestObjectInterface $object): void
    {
        $inflector = InflectorFactory::create()->build();
        /** @var UploadedFile|array<UploadedFile> $file */
        foreach ($request->files->all() as $key => $file) {
            if (\is_string($key)) {
                $propertyName = $inflector->camelize($key);
                if (!\property_exists($object, $propertyName)) {
                    continue;
                }
                $object->{$inflector->camelize($key)} = !\is_array($file) ? [$file] : $file;
            }
        }
    }

    /**
     * @return array<mixed>
     */
    public static function normalizeHeaders(Request $request): array
    {
        $resolvedHeaders = [];
        foreach ($request->headers as $key => $value) {
            $resolvedHeaders[$key] = count($value) > 1 ? $value : ($value[0] ?? null);
        }

        return $resolvedHeaders;
    }
}
