<?php

namespace RequestObjectResolverBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use RequestObjectResolverBundle\Attribute\Path;
use RequestObjectResolverBundle\Attribute\SkipValidation;
use RequestObjectResolverBundle\Exceptions\ObjectDeserializationHttpException;
use RequestObjectResolverBundle\Exceptions\RequestObjectValidationFailHttpException;
use RequestObjectResolverBundle\Resolver\RequestPathResolver;
use RequestObjectResolverBundle\Tests\Fixtures\Path\TestPathModel;
use RequestObjectResolverBundle\Tests\Fixtures\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class RequestPathResolverTest extends KernelTestCase
{
    private RequestPathResolver $resolver;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function setUp(): void
    {
        self::bootKernel();
        $this->resolver = self::getContainer()->get(RequestPathResolver::class);

        // needed for correctly reading name-converting annotations
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $serializer = new Serializer(
            [new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter)],
            [JsonEncoder::FORMAT => new JsonEncoder()]
        );
        $reflection = new \ReflectionProperty(RequestPathResolver::class, 'serializer');
        $reflection->setAccessible(true);
        $reflection->setValue($this->resolver, $serializer);
    }

    public function testWithValidationSuccess(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestPathRequest',
            type: TestPathModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                                 new Path(),
                             ]
        );
        $request = Request::create(
            uri: '/58/',
            method: Request::METHOD_GET,
        );
        $request->attributes->set('_route_params', ['object' => '58']);

        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestPathModel::class, $requestObject);
        self::assertEquals(58, $requestObject->object);
    }

    public function testWithMappingAndValidationSuccess(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestPathRequest',
            type: TestPathModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                                 new Path(map: ['id' => 'object']),
                             ]
        );
        $request = Request::create(
            uri: '/58/',
            method: Request::METHOD_GET,
        );
        $request->attributes->set('_route_params', ['id' => '58']);

        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestPathModel::class, $requestObject);
        self::assertEquals(58, $requestObject->object);
    }

    public function testWithValidationSkippedSuccess(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestPathRequest',
            type: TestPathModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                                 new Path(),
                                 new SkipValidation(),
                             ]
        );
        $request = Request::create(
            uri: '/-76/',
            method: Request::METHOD_GET,
        );
        $request->attributes->set('_route_params', ['object' => '-76']);

        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestPathModel::class, $requestObject);
        self::assertEquals(-76, $requestObject->object);
    }

    public function testWithValidationFailed(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestPathRequest',
            type: TestPathModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                                 new Path(),
                             ]
        );
        $request = Request::create(
            uri: '/-2/',
            method: Request::METHOD_GET,
        );
        $request->attributes->set('_route_params', ['object' => '-2']);

        self::expectException(RequestObjectValidationFailHttpException::class);
        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestPathModel::class, $requestObject);
    }

    public function testWithDenormalizationFailed(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestPathRequest',
            type: TestPathModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                                 new Path(),
                             ]
        );
        $request = Request::create(
            uri: '//',
            method: Request::METHOD_GET,
        );
        $request->attributes->set('_route_params', ['object' => null]);

        self::expectException(ObjectDeserializationHttpException::class);
        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestPathModel::class, $requestObject);
    }
}
