<?php

namespace RequestObjectResolverBundle\Tests;

use RequestObjectResolverBundle\Exceptions\RequestHeadersValidationFailHttpException;
use RequestObjectResolverBundle\Http\RequestCookies;
use RequestObjectResolverBundle\Http\RequestHeaders;
use RequestObjectResolverBundle\Resolver\RequestHeadersResolver;
use RequestObjectResolverBundle\Tests\Fixtures\TestKernel;
use RequestObjectResolverBundle\Tests\Fixtures\TestRequestHeaders;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestHeadersResolverTest extends KernelTestCase
{
    private RequestHeadersResolver $resolver;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function setUp(): void
    {
        self::bootKernel();
        $this->resolver = self::getContainer()->get(RequestHeadersResolver::class);
    }

    public function testResolveHeadersSuccess(): void
    {
        $arguments = new ArgumentMetadata('test', RequestHeaders::class, false, false, null);
        $request = Request::create('/', Request::METHOD_GET);
        $request->headers->set('x-test', 'test_value');

        static::assertTrue($this->resolver->supports($request, $arguments));

        $resolverResult = $this->resolver->resolve($request, $arguments);
        $headersObject = $resolverResult->current();

        static::assertInstanceOf(RequestHeaders::class, $headersObject);
        static::assertEquals('test_value', $headersObject->get('x-test'));
        static::assertNull($headersObject->get('x-test-undefined'));

        $resolverResult->next();
    }

    public function testResolveHeadersObjectFail(): void
    {
        $arguments = new ArgumentMetadata('test', RequestCookies::class, false, false, null);
        $request = Request::create('/', Request::METHOD_GET);
        $request->headers->set('x-test', 'test_value');

        static::assertFalse($this->resolver->supports($request, $arguments));
    }

    public function testResolveHeadersObjectValidationSuccess(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestHeaders::class, false, false, null);
        $request = Request::create('/', Request::METHOD_GET);
        $request->headers->set('test', 'test_value');

        static::assertTrue($this->resolver->supports($request, $arguments));

        $resolverResult = $this->resolver->resolve($request, $arguments);
        $headersObject = $resolverResult->current();

        static::assertInstanceOf(TestRequestHeaders::class, $headersObject);
        static::assertEquals('test_value', $headersObject->getTest());
        static::assertNull($headersObject->get('x-test-undefined'));

        $resolverResult->next();
    }

    public function testResolveHeadersObjectValidationFail(): void
    {
        $arguments = new ArgumentMetadata('test', TestRequestHeaders::class, false, false, null);
        $request = Request::create('/', Request::METHOD_GET);
        $request->headers->set('x-test', 'test_value');

        static::assertTrue($this->resolver->supports($request, $arguments));

        $this->expectException(RequestHeadersValidationFailHttpException::class);
        $resolverResult = $this->resolver->resolve($request, $arguments);
        $resolverResult->current();
    }
}
