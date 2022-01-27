<?php

namespace RequestObjectResolverBundle\Tests;

use PHPUnit\Framework\TestCase;
use RequestObjectResolverBundle\Http\RequestCookies;
use RequestObjectResolverBundle\Http\RequestHeaders;
use RequestObjectResolverBundle\Resolver\RequestHeadersResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestHeadersResolverTest extends TestCase
{
    public function testResolveHeadersSuccess(): void
    {
        $arguments = new ArgumentMetadata('test', RequestHeaders::class, false, false, null);
        $request = Request::create('/', Request::METHOD_GET);
        $request->headers->set('x-test', 'test_value');

        $resolver = new RequestHeadersResolver();
        static::assertTrue($resolver->supports($request, $arguments));

        $resolverResult = $resolver->resolve($request, $arguments);
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

        $resolver = new RequestHeadersResolver();
        static::assertFalse($resolver->supports($request, $arguments));
    }
}
