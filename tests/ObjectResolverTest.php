<?php

declare(strict_types=1);

namespace RequestObjectResolverBundle\Tests;

use RequestObjectResolverBundle\Attribute\Content;
use RequestObjectResolverBundle\Attribute\Form;
use RequestObjectResolverBundle\Attribute\Path;
use RequestObjectResolverBundle\Attribute\Query;
use RequestObjectResolverBundle\Attribute\SkipValidation;
use RequestObjectResolverBundle\Resolver\ObjectResolver;
use RequestObjectResolverBundle\Tests\Fixtures\Model\ContentModel;
use RequestObjectResolverBundle\Tests\Fixtures\Model\FormModel;
use RequestObjectResolverBundle\Tests\Fixtures\Model\PathModel;
use RequestObjectResolverBundle\Tests\Fixtures\Model\QueryModel;
use RequestObjectResolverBundle\Tests\Fixtures\Model\UnionModel;
use RequestObjectResolverBundle\Tests\Fixtures\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class ObjectResolverTest extends KernelTestCase
{
    private ?ObjectResolver $resolver;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function setUp(): void
    {
        self::bootKernel();
        $this->resolver = self::getContainer()->get(ObjectResolver::class);
    }

    /**
     * @param callable(object): void $testCallable
     *
     * @dataProvider successProvider
     */
    public function testSuccess(ArgumentMetadata $argument, Request $request, callable $testCallable): void
    {
        self::assertTrue($this->resolver->supports($request, $argument));

        /** @var \Generator $result */
        $result = $this->resolver->resolve($request, $argument);

        $testCallable($result->current());
    }

    public function successProvider(): \Generator
    {
        yield 'Query Test' => [
            'argument' => new ArgumentMetadata(
                name: 'QueryModel',
                type: QueryModel::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
                attributes: [
                    new Query(),
                ]
            ),
            'request' => Request::create(
                uri: '/test?id=84',
                method: Request::METHOD_GET,
            ),
            'testCallable' => function (object $result) {
                self::assertInstanceOf(QueryModel::class, $result);
                /** @var QueryModel $result */
                self::assertEquals(84, $result->id);
            },
        ];
        yield 'SkipValidation Test' => [
            'argument' => new ArgumentMetadata(
                name: 'QueryModel',
                type: QueryModel::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
                attributes: [
                    new Query(),
                    new SkipValidation(),
                ]
            ),
            'request' => Request::create(
                uri: '/test?id=-5',
                method: Request::METHOD_GET,
            ),
            'testCallable' => function (object $result) {
                self::assertInstanceOf(QueryModel::class, $result);
                /** @var QueryModel $result */
                self::assertEquals(-5, $result->id);
            },
        ];
        yield 'Path Test' => [
            'argument' => new ArgumentMetadata(
                name: 'PathModel',
                type: PathModel::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
                attributes: [
                    new Path(),
                ]
            ),
            'request' => new Request(attributes: ['_route_params' => ['id' => 192]]),
            'testCallable' => function (object $result) {
                self::assertInstanceOf(PathModel::class, $result);
                /** @var PathModel $result */
                self::assertEquals(192, $result->id);
            },
        ];
        yield 'Content Test' => [
            'argument' => new ArgumentMetadata(
                name: 'ContentModel',
                type: ContentModel::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
                attributes: [
                    new Content(),
                ]
            ),
            'request' => Request::create(
                uri: '/test',
                method: Request::METHOD_POST,
                content: \json_encode([
                    'id' => 52,
                    'name' => 'TestName',
                ])
            ),
            'testCallable' => function (object $result) {
                self::assertInstanceOf(ContentModel::class, $result);
                /** @var ContentModel $result */
                self::assertEquals(52, $result->id);
                self::assertEquals('TestName', $result->userName);
            },
        ];
        yield 'Form Test' => [
            'argument' => new ArgumentMetadata(
                name: 'FormModel',
                type: FormModel::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
                attributes: [
                    new Form(),
                ]
            ),
            'request' => Request::create(
                uri: '/test',
                method: Request::METHOD_POST,
                parameters: [
                    'id' => 121,
                ]
            ),
            'testCallable' => function (object $result) {
                self::assertInstanceOf(FormModel::class, $result);
                /** @var FormModel $result */
                self::assertEquals(121, $result->id);
            },
        ];
        yield 'Form Test with files' => [
            'argument' => new ArgumentMetadata(
                name: 'FormModel',
                type: FormModel::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
                attributes: [
                    new Form(),
                ]
            ),
            'request' => Request::create(
                uri: '/test',
                method: Request::METHOD_POST,
                parameters: [
                    'id' => 121,
                    'file' => new UploadedFile(__DIR__ . '/Fixtures/test_file_to_upload.txt', 'test.txt'),
                    'files' => [new UploadedFile(__DIR__ . '/Fixtures/test_file_to_upload.txt', 'test_array.txt')],
                ]
            ),
            'testCallable' => function (object $result) {
                self::assertInstanceOf(FormModel::class, $result);
                /** @var FormModel $result */
                self::assertEquals(121, $result->id);
                self::assertInstanceOf(UploadedFile::class, $result->file);
                self::assertEquals('test.txt', $result->file->getClientOriginalName());
                foreach ($result->files as $file) {
                    self::assertInstanceOf(UploadedFile::class, $file);
                    self::assertEquals('test_array.txt', $file->getClientOriginalName());
                }
            },
        ];
        yield 'Union Test (Query + Form)' => [
            'argument' => new ArgumentMetadata(
                name: 'UnionModel',
                type: UnionModel::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
                attributes: [
                    new Query(),
                    new Form(map: ['name' => 'username']),
                ]
            ),
            'request' => Request::create(
                uri: '/test?id=121',
                method: Request::METHOD_POST,
                parameters: [
                    'name' => 'TestName',
                ]
            ),
            'testCallable' => function (object $result) {
                self::assertInstanceOf(UnionModel::class, $result);
                /** @var UnionModel $result */
                self::assertEquals(121, $result->id);
                self::assertEquals('TestName', $result->username);
            },
        ];
        yield 'Union Test (Query + Content)' => [
            'argument' => new ArgumentMetadata(
                name: 'UnionModel',
                type: UnionModel::class,
                isVariadic: false,
                hasDefaultValue: false,
                defaultValue: null,
                attributes: [
                    new Query(),
                    new Content(map: ['name' => 'username']),
                ]
            ),
            'request' => Request::create(
                uri: '/test?id=121',
                method: Request::METHOD_POST,
                content: \json_encode([
                    'name' => 'TestName',
                ])
            ),
            'testCallable' => function (object $result) {
                self::assertInstanceOf(UnionModel::class, $result);
                /** @var UnionModel $result */
                self::assertEquals(121, $result->id);
                self::assertEquals('TestName', $result->username);
            },
        ];
    }
}
