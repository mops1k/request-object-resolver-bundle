# RequestObjectResolverBundle
This bundle can help you to deserialize incoming request parameters from symfomy http request object to your DTO objects.

Deserialized objects are validated via [symfony/validator](https://symfony.com/doc/current/validation.html), so when using such objects in
controllers, we can be sure that the data format and their set in the object are correct and ready for further processing.

Bundle can deserialize:
- query parameters
- form parameters
- json body
- uploaded files
- route parameters
- cookies (see: [RequestObjectResolverBundle\Http\RequestCookies](./src/Http/RequestCookies.php))
- headers (see: [RequestObjectResolverBundle\Http\RequestHeaders](./src/Http/RequestHeaders.php))

## Install
```bash
composer require mops1k/request-object-resolver-bundle
```

## Use
Example:

```php
<?php

use RequestObjectResolverBundle\RequestModelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;

class ExampleRequest implements RequestModelInterface
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    public ?int $id = null;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?string $name = null;
}

class ExampleController extends AbstractController
{
    #[Route('/{id}', methods: [Request::METHOD_POST])]
    public function __invoke(ExampleRequest $exampleRequest): JsonResponse
    {
        // some logic with $exampleRequest
        
        return new JsonResponse([
            'id' => $exampleRequest->id,
            'name' => $exampleRequest->name,
        ]);
    }
}
```
## Events:
### Event before request deserialization
Event `RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent`

```php
<?php

use RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent;

class ExampleListener
{
    public function beforeDeserialization(BeforeRequestObjectDeserializeEvent $event): void
    {
        if (!is_a($event->getObjectToResolve(), ExampleRequest::class, true)) {
            return;
        }

        $parameters = $event->getResolvedParameters();
        $parameters['example'] ??= 'example_value_modified';
        $event->setResolvedParameters($parameters);
    }
}
```

```yaml
services:
    ExampleListener:
        tags:
            - { name: kernel.event_listener, event: 'RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent' }
```

### Event before DTO validation
Event `RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectValidationEvent`

```php
<?php

use RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent;

class ExampleListener
{
    public function beforeValidation(BeforeRequestObjectValidationEvent $event): void
    {
        $object = $event->getObject();
        if (!is_a($object, ExampleRequest::class, true)) {
            return;
        }

        // do some stuff before object going to validation
        $object->id = 54; // example value
    }
}
```

```yaml
services:
    ExampleListener:
        tags:
            - { name: kernel.event_listener, event: 'RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectValidationEvent' }
```

## Disable concrete DTO validation
If your logic does not need automatic validation of the request object for some reason, then you can disable it
for a specific object. To do this, you need to implement the `RequestObjectResolverBundle\NonAutoValidatedRequestModelInterface` interface.

Example:

```php
<?php

use RequestObjectResolverBundle\NonAutoValidatedRequestModelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExampleRequest implements NonAutoValidatedRequestModelInterface
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    public ?int $id = null;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?string $name = null;
}

class ExampleController extends AbstractController
{
    #[Route('/{id}', methods: [Request::METHOD_POST])]
    public function __invoke(ExampleRequest $exampleRequest, ValidatorInterface $validator): JsonResponse
    {
        //some logic with $exampleRequest
        $exampleRequest->id ??= 1;

        // run validation when you need it
        $violationList = $validator->validate($exampleRequest);
        // ...

        return new JsonResponse([
            'id' => $exampleRequest->id,
            'name' => $exampleRequest->name,
        ]);
    }
}
```

## Validation groups
If you want to use validation groups, then implement `\RequestObjectResolverBundle\ValidationGroupsInterface`.

Example:
```php

<?php

use RequestObjectResolverBundle\ValidationGroupsInterface;

class ExampleRequest implements ValidationGroupsInterface
{
    #[Assert\NotNull(groups: ['ExampleGroup'])]
    #[Assert\GreaterThan(0)]
    public ?int $id = null;
    
    #[Assert\NotNull]
    #[Assert\NotBlank]
    public ?string $name = null;
    
    public static function validationGroups() : ?array
    {
        return ['ExampleGroup'];
    }
}
```
