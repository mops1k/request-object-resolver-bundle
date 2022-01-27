<?php

namespace RequestObjectResolverBundle\Tests;

use PHPUnit\Framework\TestCase;
use RequestObjectResolverBundle\Http\RequestCookies;
use RequestObjectResolverBundle\Http\RequestHeaders;
use RequestObjectResolverBundle\Resolver\RequestCookiesResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestCookiesResolverTest extends TestCase
{
    public function testResolveCookiesSuccess(): void
    {
        $arguments = new ArgumentMetadata('test', RequestCookies::class, false, false, null);
        $request = Request::create('/', Request::METHOD_GET, cookies: ['x-test' => 'test_value']);

        $resolver = new RequestCookiesResolver();
        static::assertTrue($resolver->supports($request, $arguments));

        $resolverResult = $resolver->resolve($request, $arguments);
        $cookiesObject = $resolverResult->current();

        static::assertInstanceOf(RequestCookies::class, $cookiesObject);
        static::assertTrue($cookiesObject->has('x-test'));
        static::assertFalse($cookiesObject->has('x-test-undefined'));
        static::assertEquals('test_value', $cookiesObject->get('x-test'));
        static::assertNull($cookiesObject->get('x-test-undefined'));
        static::assertCount(1, $cookiesObject->all());

        $resolverResult->next();
    }

    public function testResolveCookiesObjectFail(): void
    {
        $arguments = new ArgumentMetadata('test', RequestHeaders::class, false, false, null);
        $request = Request::create('/', Request::METHOD_GET, cookies: ['x-test' => 'test_value']);

        $resolver = new RequestCookiesResolver();
        static::assertFalse($resolver->supports($request, $arguments));
    }
}
