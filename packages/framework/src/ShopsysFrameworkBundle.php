<?php

namespace Shopsys\FrameworkBundle;

use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\AllServicesPublicCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterCronModulesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterPluginCrudExtensionsCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterPluginDataFixturesCompilerPass;
use Shopsys\FrameworkBundle\DependencyInjection\Compiler\RegisterProductFeedConfigsCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopsysFrameworkBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterCronModulesCompilerPass());
        $container->addCompilerPass(new RegisterPluginCrudExtensionsCompilerPass());
        $container->addCompilerPass(new RegisterPluginDataFixturesCompilerPass());
        $container->addCompilerPass(new RegisterProductFeedConfigsCompilerPass());

        if ($container->getParameter('kernel.environment') === EnvironmentType::TEST) {
            $container->addCompilerPass(new AllServicesPublicCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING);
        }
    }
}
