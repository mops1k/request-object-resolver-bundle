<?php

namespace RequestObjectResolverBundle;

use RequestObjectResolverBundle\DependencyInjection\CompilerPass\ResolverCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RequestObjectResolverBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ResolverCompilerPass());
    }
}
