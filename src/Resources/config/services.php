<?php

declare(strict_types=1);

use RequestObjectResolverBundle\Resolver\RequestContentResolver;
use RequestObjectResolverBundle\Resolver\RequestCookiesResolver;
use RequestObjectResolverBundle\Resolver\RequestHeadersResolver;
use RequestObjectResolverBundle\Resolver\RequestObjectResolver;
use RequestObjectResolverBundle\Resolver\RequestPathResolver;
use RequestObjectResolverBundle\Resolver\RequestQueryResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RequestHeadersResolver::class)
             ->deprecate(
                 'mops1k/request-object-resolver-bundle',
                 '1.1.0',
                 'Service %service_id% is depreceted and will be removed in next minor version'
             )
             ->args([
                        service('validator'),
                    ])
             ->tag('controller.argument_value_resolver', [
                 'priority' => 50,
             ])
    ;

    $services->set(RequestCookiesResolver::class)
             ->deprecate(
                 'mops1k/request-object-resolver-bundle',
                 '1.1.0',
                 'Service %service_id% is depreceted and will be removed in next minor version'
             )
             ->tag('controller.argument_value_resolver', [
                 'priority' => 50,
             ])
    ;

    $services->set(RequestObjectResolver::class)
             ->deprecate(
                 'mops1k/request-object-resolver-bundle',
                 '1.1.0',
                 'Service %service_id% is depreceted and will be removed in next minor version'
             )
             ->args([
                        service('serializer'),
                        service('validator'),
                        service('event_dispatcher'),
                    ])
             ->tag('controller.argument_value_resolver', [
                 'priority' => 50,
             ])
    ;

    $services->set(RequestContentResolver::class)
             ->args([
                        service('serializer'),
                        service('validator'),
                    ])
             ->tag('controller.argument_value_resolver', [
                 'priority' => 200,
             ])
    ;

    $services->set(RequestQueryResolver::class)
             ->args([
                        service('serializer'),
                        service('validator'),
                    ])
             ->tag('controller.argument_value_resolver', [
                 'priority' => 100,
             ])
    ;

    $services->set(RequestPathResolver::class)
             ->args([
                        service('serializer'),
                        service('validator'),
                    ])
             ->tag('controller.argument_value_resolver', [
                 'priority' => 50,
             ])
    ;
};
