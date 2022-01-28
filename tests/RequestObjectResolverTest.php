<?php

namespace RequestObjectResolverBundle\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent;
use RequestObjectResolverBundle\Exceptions\RequestObjectValidationFailHttpException;
use RequestObjectResolverBundle\Interfaces\RequestObjectInterface;
use RequestObjectResolverBundle\Resolver\RequestObjectResolver;
use RequestObjectResolverBundle\Tests\Fixtures\TestKernel;
use RequestObjectResolverBundle\Tests\Fixtures\TestListener;
use RequestObjectResolverBundle\Tests\Fixtures\TestRequestObject;
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

    public function testRequestResolveSuccess(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestObject::class, false, false, null);
        $request = Request::create(
            '/?test_query=test_query_value',
            Request::METHOD_GET,
            parameters: ['test' => 'test_value'],
            files: ['test_file' => new UploadedFile(__DIR__ . '/Fixtures/test_file_to_upload.txt', 'test.txt')],
            content: \json_encode(['test_json' => 'test_json_value'], JSON_THROW_ON_ERROR),
        );

        static::assertTrue($this->resolver->supports($request, $arguments));

        $resolverResult = $this->resolver->resolve($request, $arguments);
        $requestObject = $resolverResult->current();

        static::assertInstanceOf(RequestObjectInterface::class, $requestObject);
        static::assertInstanceOf(TestRequestObject::class, $requestObject);
        static::assertEquals('test_value', $requestObject->test);
        static::assertEquals('test_json_value', $requestObject->testJson);
        static::assertEquals('test_query_value', $requestObject->testQuery);
        static::assertCount(1, $requestObject->testFile);
        static::assertInstanceOf(UploadedFile::class, $requestObject->testFile[0]);

        $resolverResult->next();
    }

    public function testRequestResolveContentAreNotJsonAndFileHasNoPropertySuccess(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestObject::class, false, false, null);
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

        static::assertInstanceOf(RequestObjectInterface::class, $requestObject);
        static::assertInstanceOf(TestRequestObject::class, $requestObject);
        static::assertEquals('test_value', $requestObject->test);
        static::assertNull($requestObject->testJson);
        static::assertEquals('test_query_value', $requestObject->testQuery);
        static::assertCount(0, $requestObject->testFile);

        $resolverResult->next();
    }

    public function testRequestResolveSuccessWithListener(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestObject::class, false, false, null);
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

        static::assertInstanceOf(RequestObjectInterface::class, $requestObject);
        static::assertInstanceOf(TestRequestObject::class, $requestObject);
        static::assertEquals('test_value_modified', $requestObject->test);
        static::assertEquals('test_json_value', $requestObject->testJson);
        static::assertEquals('test_query_value', $requestObject->testQuery);
        static::assertCount(1, $requestObject->testFile);
        static::assertInstanceOf(UploadedFile::class, $requestObject->testFile[0]);

        $resolverResult->next();
    }

    public function testRequestResolveFail(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestObject::class, false, false, null);
        $request = Request::create(
            '/',
            Request::METHOD_GET,
        );

        static::assertTrue($this->resolver->supports($request, $arguments));

        try {
            $resolverResult = $this->resolver->resolve($request, $arguments);
            $resolverResult->current();
            static::fail('Expected exception not thrown.');
        } catch (RequestObjectValidationFailHttpException $e) {
            static::assertCount(4, $e->getErrors());
            static::assertEquals(400, $e->getStatusCode());
        }
    }
}
