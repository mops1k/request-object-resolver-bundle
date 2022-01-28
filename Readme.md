# RequestObjectResolverBundle
Symfony библиотека, позволяющая десериализовать параметры запроса из объекта symfony в специально подготовленные объекты.

Десериализованные объекты проходят валидацию через [symfony/validator](https://symfony.com/doc/current/validation.html), поэтому при использовании таких объектов в
контроллерах мы можем быть уверенны, что формат данных и их набор в объекте верны и готовы к дальнейшей обработке.

Библиотека может десериализовать:
- query параметры
- параметры формы (parameters)
- json тело запроса
- загруженные файлы
- параметры роутинга
- куки (см. [Kvarta\RequestObjectResolverBundle\Http\RequestCookies](./src/Http/RequestCookies.php))
- хедеры (см. [Kvarta\RequestObjectResolverBundle\Http\RequestHeaders](./src/Http/RequestHeaders.php))

## Установка
1. Добавить в composer.json
```json
{
  "repositories": [
    {
      "type": "composer",
      "url": "https://git.structure.pik-broker.ru/api/v4/group/184/-/packages/composer/packages.json"
    }
  ]
}
```
2. Выполнить
```bash
composer require kvarta/request-object-resolver-bundle
```

## Использование
Пример:
```php
<?php

use Kvarta\RequestObjectResolverBundle\Interfaces\RequestObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Routing\Annotation\Route;

class ExampleRequest implements RequestObjectInterface
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
        // какая-то логика работы с $exampleRequest
        
        return new JsonResponse([
            'id' => $exampleRequest->id,
            'name' => $exampleRequest->name,
        ]);
    }
}
```

## Предварительная обработка запроса до выполнения десериализации и валидации
Для этого мы создадим EventListener и повесим его на событие `Kvarta\RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent`

```php
<?php

use Kvarta\RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent;

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
            - { name: kernel.event_listener, event: 'Kvarta\RequestObjectResolverBundle\EventDispatcher\BeforeRequestObjectDeserializeEvent' }
```

## @TODO
- [ ] добавить валидацию для объектов headers
- [ ] добавить валидацию для объектов cookies