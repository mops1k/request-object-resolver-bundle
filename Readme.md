# RequestObjectResolverBundle
This bundle can help you to deserialize incoming request parameters from symfomy http request object to your DTO objects.

Deserialized objects are validated via [symfony/validator](https://symfony.com/doc/current/validation.html), so when using such objects in
controllers, we can be sure that the data format and their set in the object are correct and ready for further processing.

Bundle can deserialize:
- route parameters (attribute `RequestObjectResolverBundle\Attribute\Path`)
- query parameters (attribute `RequestObjectResolverBundle\Attribute\Query`)
- content body (supports all symfony serializer formats)  (attribute `RequestObjectResolverBundle\Attribute\Content`)
- form parameters (attribute `RequestObjectResolverBundle\Attribute\Form`)
- uploaded files (attribute `RequestObjectResolverBundle\Attribute\Form`)

## Install
```bash
composer require mops1k/request-object-resolver-bundle
```

## Use
Example:

```php
<?php

use RequestObjectResolverBundle\Attribute\Query;
use RequestObjectResolverBundle\Attribute\Path;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class ExampleRequest
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    public ?int $id = null;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?string $name = null;
}

/**
 * Request path example: /25?name=Julian
 */
class ExampleController extends AbstractController
{
    #[Route('/{id}', methods: [Request::METHOD_GET])]
    public function __invoke(#[Query, Path] ExampleRequest $exampleRequest): JsonResponse
    {
        // some logic with $exampleRequest
        
        return new JsonResponse([
            'id' => $exampleRequest->id,
            'name' => $exampleRequest->name,
        ]);
    }
}
```

## Map field to another name
Whole library attributes have a map parameter. With this parameter you can map from one field name to another.

Example:

```php
<?php

use RequestObjectResolverBundle\Attribute\Query;
use RequestObjectResolverBundle\Attribute\Path;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class ExampleRequest
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    public ?int $id = null;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?string $title = null;
}

/**
 * Request path example: /25?name=Julian
 */
class ExampleController extends AbstractController
{
    #[Route('/{id}', methods: [Request::METHOD_GET])]
    public function __invoke(#[Query(map: ['name' => 'title']), Path] ExampleRequest $exampleRequest): JsonResponse
    {
        // some logic with $exampleRequest
        
        return new JsonResponse([
            'id' => $exampleRequest->id,
            'title' => $exampleRequest->name,
        ]);
    }
}
```

## Skip dto validation
If your logic does not need automatic validation of the request object for some reason, then you can disable it
with `RequestObjectResolverBundle\Attribute\SkipValidation` attribute.

Example:

```php
<?php

use RequestObjectResolverBundle\Attribute\Query;
use RequestObjectResolverBundle\Attribute\Path;
use RequestObjectResolverBundle\Attribute\SkipValidation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class ExampleRequest
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    public ?int $id = null;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?string $name = null;
}

/**
 * Request path example: /-1?name=
 */
class ExampleController extends AbstractController
{
    #[Route('/{id}', methods: [Request::METHOD_GET])]
    public function __invoke(#[Query, Path, SkipValidation] ExampleRequest $exampleRequest): JsonResponse
    {
        // some logic with $exampleRequest
        
        return new JsonResponse([
            'id' => $exampleRequest->id,
            'name' => $exampleRequest->name,
        ]);
    }
}
```

## Validation groups
If you want to use validation groups, then use attribute `\RequestObjectResolverBundle\Attribute\ValidationGroups`.

Example:

```php
<?php

use RequestObjectResolverBundle\Attribute\Query;
use RequestObjectResolverBundle\Attribute\Path;
use RequestObjectResolverBundle\Attribute\ValidationGroups;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class ExampleRequest
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(0, groups: ['default'])]
    public ?int $id = null;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?string $name = null;
}

/**
 * Request path example: /25?name=Julian
 */
class ExampleController extends AbstractController
{
    #[Route('/{id}', methods: [Request::METHOD_POST])]
    public function __invoke(#[Query, Path, ValidationGroups(groups: 'default')] ExampleRequest $exampleRequest): JsonResponse
    {
        // some logic with $exampleRequest
        
        return new JsonResponse([
            'id' => $exampleRequest->id,
            'name' => $exampleRequest->name,
        ]);
    }
}
```

## Serialization context
If you want to add some serialization context, then in attributes you can set `serializationContext` property.

Example:

```php
<?php

use RequestObjectResolverBundle\Attribute\Query;
use RequestObjectResolverBundle\Attribute\Path;
use RequestObjectResolverBundle\Attribute\ValidationGroups;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ExampleRequest
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    #[Groups(['default'])]
    public ?int $id = null;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?string $name = null;
}

/**
 * Request path example: /25?name=Julian
 */
class ExampleController extends AbstractController
{
    #[Route('/{id}', methods: [Request::METHOD_POST])]
    public function __invoke(#[Query(serializerContext: ['groups': ['default']]), Path] ExampleRequest $exampleRequest): JsonResponse
    {
        // some logic with $exampleRequest
        
        return new JsonResponse([
            'id' => $exampleRequest->id,
            'name' => $exampleRequest->name,
        ]);
    }
}
```

If you want to set context to all request parts which you want to deserialize to DTO,
use `\RequestObjectResolverBundle\Attribute\SerializationContext` attribute.

```php
<?php

use RequestObjectResolverBundle\Attribute\Query;
use RequestObjectResolverBundle\Attribute\Path;
use RequestObjectResolverBundle\Attribute\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ExampleRequest
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    #[Groups(['default'])]
    public ?int $id = null;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?string $name = null;
}

/**
 * Request path example: /25?name=Julian
 */
class ExampleController extends AbstractController
{
    #[Route('/{id}', methods: [Request::METHOD_POST])]
    public function __invoke(#[Query, Path, SerializationContext(['groups': ['default']])] ExampleRequest $exampleRequest): JsonResponse
    {
        // some logic with $exampleRequest
        
        return new JsonResponse([
            'id' => $exampleRequest->id,
            'name' => $exampleRequest->name,
        ]);
    }
}
```

## Overriding values with request parts combination
These are table of request parts priority overriding (if have same key name):

Request part|Priority (lower value = higher priority)
------------|----------------------------------------
Path|30
Query|20
Form|10
Content|0

Example. If you handle `Path`, `Query` and `Content` in same object and all of them have same field (id for example),
then resulting field value will be from `Form` request part.
