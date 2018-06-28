<?php

namespace Shopsys\FrameworkBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AllServicesPublicCompilerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $definition) {
            if (!$definition->isAbstract() && !$definition->isSynthetic()) {
                $definition->setPublic(true);
            }
        }
        foreach ($container->getAliases() as $alias) {
            $alias->setPublic(true);
        }
    }
}
