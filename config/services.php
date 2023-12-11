<?php

declare(strict_types=1);

use RequestObjectResolverBundle\Attribute\Content;
use RequestObjectResolverBundle\Attribute\Form;
use RequestObjectResolverBundle\Attribute\Path;
use RequestObjectResolverBundle\Attribute\Query;
use RequestObjectResolverBundle\Chain\ChainManager;
use RequestObjectResolverBundle\Chain\ContentResolver;
use RequestObjectResolverBundle\Chain\FormResolver;
use RequestObjectResolverBundle\Chain\PathResolver;
use RequestObjectResolverBundle\Chain\QueryResolver;
use RequestObjectResolverBundle\Resolver\ObjectResolver;
use RequestObjectResolverBundle\Resolver\RequestCookiesResolver;
use RequestObjectResolverBundle\Resolver\RequestHeadersResolver;
use RequestObjectResolverBundle\Resolver\RequestObjectResolver;
use RequestObjectResolverBundle\Serializer\UploadedFileDenormalizer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // default attributes which can be handled by resolver
    $services->set(Path::class)
        ->tag('request_object.attribute');
    $services->set(Query::class)
        ->tag('request_object.attribute');
    $services->set(Form::class)
        ->tag('request_object.attribute');
    $services->set(Content::class)
        ->tag('request_object.attribute');

    // resolvers for chain (links)
    $services->set(ContentResolver::class)
        ->args([
            service('serializer'),
        ])
        ->tag('request_object.resolver', ['priority' => 0]);

    $services->set(FormResolver::class)
        ->args([
            service('serializer'),
        ])
        ->tag('request_object.resolver', ['priority' => 10]);

    $services->set(PathResolver::class)
        ->args([
            service('serializer'),
        ])
        ->tag('request_object.resolver', ['priority' => 20]);

    $services->set(QueryResolver::class)
        ->args([
            service('serializer'),
        ])
        ->tag('request_object.resolver', ['priority' => 30]);

    // chain entrypoint
    $services->set(ChainManager::class)
        ->args([
            tagged_iterator('request_object.resolver', defaultPriorityMethod: 'getPriority'),
        ]);

    $services->set(ObjectResolver::class)
        ->args([
            service('validator'),
            service(ChainManager::class),
            tagged_iterator('request_object.attribute'),
        ])
        ->tag('controller.argument_value_resolver', [
            'priority' => 50,
        ])
    ;

    $services->set(UploadedFileDenormalizer::class)
        ->tag('serializer.normalizer')
    ;
};
