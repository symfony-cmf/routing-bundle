<?php

namespace Symfony\Cmf\Bundle\RoutingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\ChainRouterPass;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\RouteEnhancerPass;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\SetRouterPass;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\RoutePass;

/**
 * Bundle class
 */
class CmfRoutingBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ChainRouterPass());
        $container->addCompilerPass(new RouteEnhancerPass());
        $container->addCompilerPass(new SetRouterPass());
        $container->addCompilerPass(new RoutePass());
    }
}
