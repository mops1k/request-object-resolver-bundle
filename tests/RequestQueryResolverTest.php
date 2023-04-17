<?php

namespace RequestObjectResolverBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use RequestObjectResolverBundle\Attribute\Query;
use RequestObjectResolverBundle\Attribute\SkipValidation;
use RequestObjectResolverBundle\Exceptions\RequestObjectValidationFailHttpException;
use RequestObjectResolverBundle\Resolver\RequestQueryResolver;
use RequestObjectResolverBundle\Tests\Fixtures\Query\TestQueryModel;
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

class RequestQueryResolverTest extends KernelTestCase
{
    private RequestQueryResolver $resolver;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function setUp(): void
    {
        self::bootKernel();
        $this->resolver = self::getContainer()->get(RequestQueryResolver::class);

        // needed for correctly reading name-converting annotations
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $serializer = new Serializer(
            [new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter)],
            [JsonEncoder::FORMAT => new JsonEncoder()]
        );
        $reflection = new \ReflectionProperty(RequestQueryResolver::class, 'serializer');
        $reflection->setAccessible(true);
        $reflection->setValue($this->resolver, $serializer);
    }

    public function testWithValidationSuccess(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestQueryRequest',
            type: TestQueryModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                      new Query(),
                  ]
        );
        $request = Request::create(
            uri: '/?test=string&number=5',
            method: Request::METHOD_GET,
        );

        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestQueryModel::class, $requestObject);
        self::assertEquals('string', $requestObject->test);
        self::assertEquals(5, $requestObject->number);
    }

    public function testWithMappingAndValidationSuccess(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestQueryRequest',
            type: TestQueryModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                      new Query(map: ['id' => 'number']),
                  ]
        );
        $request = Request::create(
            uri: '/?test=string&id=5',
            method: Request::METHOD_GET,
        );

        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestQueryModel::class, $requestObject);
        self::assertEquals('string', $requestObject->test);
        self::assertEquals(5, $requestObject->number);
    }

    public function testWithValidationSkippedSuccess(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestQueryRequest',
            type: TestQueryModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                      new Query(),
                      new SkipValidation(),
                  ]
        );
        $request = Request::create(
            uri: '/?test=false&number=15',
            method: Request::METHOD_GET,
        );

        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestQueryModel::class, $requestObject);
        self::assertEquals('false', $requestObject->test);
        self::assertEquals(15, $requestObject->number);
    }

    public function testWithValidationFailed(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestQueryRequest',
            type: TestQueryModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                      new Query(),
                  ]
        );
        $request = Request::create(
            uri: '/?test=string&number=15',
            method: Request::METHOD_GET,
        );

        self::expectException(RequestObjectValidationFailHttpException::class);
        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestQueryModel::class, $requestObject);
    }
}
