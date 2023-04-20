<?php

namespace RequestObjectResolverBundle\DependencyInjection\CompilerPass;

use RequestObjectResolverBundle\Chain\ResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ResolverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if (!$definition->getClass() || !class_exists($definition->getClass())) {
                continue;
            }

            try {
                $class = new \ReflectionClass($definition->getClass());
                if (!$class->implementsInterface(ResolverInterface::class)) {
                    continue;
                }

                $definition->addTag('request_object.resolver');
                $container->setDefinition($id, $definition);
            } catch (\Throwable) {
            }
        }
    }
}
