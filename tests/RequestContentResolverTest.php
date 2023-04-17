<?php

namespace RequestObjectResolverBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use RequestObjectResolverBundle\Attribute\Content;
use RequestObjectResolverBundle\Attribute\SkipValidation;
use RequestObjectResolverBundle\Exceptions\RequestObjectDeserializationHttpException;
use RequestObjectResolverBundle\Exceptions\RequestObjectValidationFailHttpException;
use RequestObjectResolverBundle\Resolver\RequestContentResolver;
use RequestObjectResolverBundle\Tests\Fixtures\Content\TestContentModel;
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

class RequestContentResolverTest extends KernelTestCase
{
    private RequestContentResolver $resolver;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function setUp(): void
    {
        self::bootKernel();
        $this->resolver = self::getContainer()->get(RequestContentResolver::class);

        // needed for correctly reading name-converting annotations
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $serializer = new Serializer(
            [new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter)],
            [JsonEncoder::FORMAT => new JsonEncoder()]
        );
        $reflection = new \ReflectionProperty(RequestContentResolver::class, 'serializer');
        $reflection->setAccessible(true);
        $reflection->setValue($this->resolver, $serializer);
    }

    public function testWithValidationSuccess(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestContentRequest',
            type: TestContentModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                new Content(),
            ]
        );
        $request = Request::create(
            '/',
            Request::METHOD_GET,
            content: \json_encode(['test' => 'test_json_value', 'test_bool' => true], JSON_THROW_ON_ERROR),
        );

        $resolverResult = $this->resolver->resolve($request, $argument);

        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestContentModel::class, $requestObject);
        self::assertEquals('test_json_value', $requestObject->test);
        self::assertTrue($requestObject->testBool);
    }

    public function testWithValidationSkippedSuccess(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestContentRequest',
            type: TestContentModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                      new Content(),
                      new SkipValidation(),
                  ]
        );
        $request = Request::create(
            '/',
            Request::METHOD_GET,
            content: \json_encode(['test' => '', 'test_bool' => false], JSON_THROW_ON_ERROR),
        );

        $resolverResult = $this->resolver->resolve($request, $argument);
        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestContentModel::class, $requestObject);
        self::assertEquals('', $requestObject->test);
        self::assertFalse($requestObject->testBool);
    }

    public function testWithFieldsMapping(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestContentRequest',
            type: TestContentModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                      new Content(map: [
                          'retest' => 'test',
                                       ]),
                      new SkipValidation(),
                  ]
        );
        $request = Request::create(
            '/',
            Request::METHOD_GET,
            content: \json_encode(['retest' => 'this is test value', 'test_bool' => false], JSON_THROW_ON_ERROR),
        );

        $resolverResult = $this->resolver->resolve($request, $argument);
        $requestObject = $resolverResult[0] ?? null;
        self::assertInstanceOf(TestContentModel::class, $requestObject);
        self::assertEquals('this is test value', $requestObject->test);
        self::assertFalse($requestObject->testBool);
    }

    public function testWithValidationFailed(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestContentRequest',
            type: TestContentModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                new Content(),
            ]
        );
        $request = Request::create(
            '/',
            Request::METHOD_GET,
            content: \json_encode(['test' => '', 'test_bool' => false], JSON_THROW_ON_ERROR),
        );

        self::expectException(RequestObjectValidationFailHttpException::class);
        $this->resolver->resolve($request, $argument);
    }

    public function testWithDeserializationFailed(): void
    {
        $argument = new ArgumentMetadata(
            name: 'TestContentRequest',
            type: TestContentModel::class,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                new Content(),
            ]
        );
        $request = Request::create(
            '/',
            Request::METHOD_GET,
            content: \json_encode(['test' => '', 'test_bool' => null], JSON_THROW_ON_ERROR),
        );

        self::expectException(RequestObjectDeserializationHttpException::class);
        $this->resolver->resolve($request, $argument);
    }
}
