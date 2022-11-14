<?php

namespace RequestObjectResolverBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent;
use RequestObjectResolverBundle\Exceptions\RequestObjectDeserializationHttpException;
use RequestObjectResolverBundle\Exceptions\RequestObjectTypeErrorHttpException;
use RequestObjectResolverBundle\Exceptions\RequestObjectValidationFailHttpException;
use RequestObjectResolverBundle\NonAutoValidatedRequestModelInterface;
use RequestObjectResolverBundle\RequestModelInterface;
use RequestObjectResolverBundle\Resolver\RequestObjectResolver;
use RequestObjectResolverBundle\Tests\Fixtures\TestKernel;
use RequestObjectResolverBundle\Tests\Fixtures\TestListener;
use RequestObjectResolverBundle\Tests\Fixtures\TestNonAutoValidatedRequestModel;
use RequestObjectResolverBundle\Tests\Fixtures\TestRequestModel;
use RequestObjectResolverBundle\Tests\Fixtures\TestRequestModelWithGroup;
use RequestObjectResolverBundle\ValidationGroupsInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class RequestObjectResolverTest extends KernelTestCase
{
    private RequestObjectResolver $resolver;
    private EventDispatcher $dispatcher;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function setUp(): void
    {
        self::bootKernel();
        $this->resolver = self::getContainer()->get(RequestObjectResolver::class);
        $this->dispatcher = new EventDispatcher();

        // needed for correctly reading name-converting annotations
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $serializer = new Serializer(
            [new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter)],
            ['json' => new JsonEncoder()]
        );
        $reflection = new \ReflectionProperty(RequestObjectResolver::class, 'serializer');
        $reflection->setAccessible(true);
        $reflection->setValue($this->resolver, $serializer);

        $reflection = new \ReflectionProperty(RequestObjectResolver::class, 'eventDispatcher');
        $reflection->setAccessible(true);
        $reflection->setValue($this->resolver, $this->dispatcher);
    }

    /**
     * @dataProvider requestResolveSuccessParametersProvider
     */
    public function testRequestResolveSuccess(mixed $parameter, string $expectedValue): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestModel::class, false, false, null);
        $request = Request::create(
            '/?test_query=test_query_value',
            Request::METHOD_GET,
            parameters: ['test' => $parameter],
            files: ['test_file' => new UploadedFile(__DIR__ . '/Fixtures/test_file_to_upload.txt', 'test.txt')],
            content: \json_encode(['test_json' => 'test_json_value'], JSON_THROW_ON_ERROR),
        );

        static::assertTrue($this->resolver->supports($request, $arguments));

        $resolverResult = $this->resolver->resolve($request, $arguments);
        $requestObject = $resolverResult->current();

        static::assertInstanceOf(RequestModelInterface::class, $requestObject);
        static::assertInstanceOf(TestRequestModel::class, $requestObject);
        static::assertEquals($expectedValue, $requestObject->test);
        static::assertEquals('test_json_value', $requestObject->testJson);
        static::assertEquals('test_query_value', $requestObject->testQuery);
        static::assertCount(1, $requestObject->testFile);
        static::assertInstanceOf(UploadedFile::class, $requestObject->testFile[0]);

        $resolverResult->next();
    }

    public function testNonAutoValidatedRequestResolveSuccess(): void
    {
        $arguments = new ArgumentMetadata('test', TestNonAutoValidatedRequestModel::class, false, false, null);
        $request = Request::create(
            '/',
            Request::METHOD_GET,
        );
        static::assertTrue($this->resolver->supports($request, $arguments));

        $resolverResult = $this->resolver->resolve($request, $arguments);
        $requestObject = $resolverResult->current();
        static::assertInstanceOf(RequestModelInterface::class, $requestObject);
        static::assertInstanceOf(NonAutoValidatedRequestModelInterface::class, $requestObject);
        static::assertInstanceOf(TestNonAutoValidatedRequestModel::class, $requestObject);

        static::assertNull($requestObject->test);
        static::assertNull($requestObject->testJson);
        static::assertNull($requestObject->testQuery);
        static::assertEquals([], $requestObject->testFile);

        $resolverResult->next();
    }

    public function testWithGroupRequestResolveSuccess(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestModelWithGroup::class, false, false, null);
        $request = Request::create(
            '/?id=5',
            Request::METHOD_GET,
        );
        static::assertTrue($this->resolver->supports($request, $arguments));

        $resolverResult = $this->resolver->resolve($request, $arguments);
        $requestObject = $resolverResult->current();
        static::assertInstanceOf(RequestModelInterface::class, $requestObject);
        static::assertInstanceOf(ValidationGroupsInterface::class, $requestObject);
        static::assertInstanceOf(TestRequestModelWithGroup::class, $requestObject);

        static::assertEquals(5, $requestObject->id);
        static::assertEmpty($requestObject->test);
    }

    public function testWithGroupRequestResolveFail(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestModelWithGroup::class, false, false, null);
        $request = Request::create(
            '/?test=string',
            Request::METHOD_GET,
        );
        static::assertTrue($this->resolver->supports($request, $arguments));

        try {
            $resolverResult = $this->resolver->resolve($request, $arguments);
            $resolverResult->current();
            static::fail('Expected exception not thrown.');
        } catch (RequestObjectValidationFailHttpException $e) {
            static::assertCount(1, $e->getErrors());
            static::assertEquals(400, $e->getStatusCode());
            static::assertStringContainsString('Request validation failed.', $e->getMessage());
        }
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function requestResolveSuccessParametersProvider(): iterable
    {
        yield 'test with int' => ['parameter' => 1, 'expectedValue' => '1'];
        yield 'test with string' => ['parameter' => 'test_value', 'expectedValue' => 'test_value'];
    }

    public function testRequestResolveContentAreNotJsonAndFileHasNoPropertySuccess(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestModel::class, false, false, null);
        $request = Request::create(
            '/?test_query=test_query_value',
            Request::METHOD_GET,
            parameters: ['test' => 'test_value'],
            files: ['test_file_not_mapped' => new UploadedFile(__DIR__ . '/Fixtures/test_file_to_upload.txt', 'test.txt')],
            content: 'test_content',
        );

        static::assertTrue($this->resolver->supports($request, $arguments));

        $resolverResult = $this->resolver->resolve($request, $arguments);
        $requestObject = $resolverResult->current();

        static::assertInstanceOf(RequestModelInterface::class, $requestObject);
        static::assertInstanceOf(TestRequestModel::class, $requestObject);
        static::assertEquals('test_value', $requestObject->test);
        static::assertNull($requestObject->testJson);
        static::assertEquals('test_query_value', $requestObject->testQuery);
        static::assertCount(0, $requestObject->testFile);

        $resolverResult->next();
    }

    public function testRequestResolveSuccessWithListener(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestModel::class, false, false, null);
        $request = Request::create(
            '/?test_query=test_query_value',
            Request::METHOD_GET,
            parameters: ['test' => 'test_value'],
            files: ['test_file' => new UploadedFile(__DIR__ . '/Fixtures/test_file_to_upload.txt', 'test.txt')],
            content: \json_encode(['test_json' => 'test_json_value'], JSON_THROW_ON_ERROR),
        );

        $this->dispatcher->addListener(BeforeRequestObjectDeserializeEvent::class, [new TestListener(), 'beforeDeserialization']);

        static::assertTrue($this->resolver->supports($request, $arguments));

        $resolverResult = $this->resolver->resolve($request, $arguments);
        $requestObject = $resolverResult->current();

        static::assertInstanceOf(RequestModelInterface::class, $requestObject);
        static::assertInstanceOf(TestRequestModel::class, $requestObject);
        static::assertEquals('test_value_modified', $requestObject->test);
        static::assertEquals('test_json_value', $requestObject->testJson);
        static::assertEquals('test_query_value', $requestObject->testQuery);
        static::assertCount(1, $requestObject->testFile);
        static::assertInstanceOf(UploadedFile::class, $requestObject->testFile[0]);

        $resolverResult->next();
    }


    /**
     * @dataProvider requestResolveFailParametersProvider
     */
    public function testRequestResolveFail(mixed $parameter, string $expectedExceptionMessage): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestModel::class, false, false, null);
        $request = Request::create(
            '/',
            Request::METHOD_GET,
            parameters: ['test' => $parameter],
        );

        static::assertTrue($this->resolver->supports($request, $arguments));

        try {
            $resolverResult = $this->resolver->resolve($request, $arguments);
            $resolverResult->current();
            static::fail('Expected exception not thrown.');
        } catch (RequestObjectValidationFailHttpException $e) {
            static::assertCount(2, $e->getErrors());
            static::assertEquals(400, $e->getStatusCode());
            static::assertStringContainsString($expectedExceptionMessage, $e->getMessage());
        } catch (RequestObjectTypeErrorHttpException $e) {
            static::assertEquals('test', $e->getField());
            static::assertEquals($expectedExceptionMessage, $e->getMessage());
            static::assertEquals(400, $e->getStatusCode());
        } catch (RequestObjectDeserializationHttpException $e) {
            static::assertEquals(400, $e->getStatusCode());
        }
    }

    /**
     * @return iterable<array<mixed>>
     */
    public function requestResolveFailParametersProvider(): iterable
    {
        yield 'test with string' => ['parameter' => 'test_value', 'expectedExceptionMessage' => 'Request validation failed.'];
        yield 'test with null' => ['parameter' => null, 'expectedExceptionMessage' => 'Passed a value with type null, expected type string'];
        yield 'test with array' => ['parameter' => [], 'expectedExceptionMessage' => 'Passed a value with type array, expected type string'];
    }
}
