<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection\Compiler\ChainRouterPass;
use Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection\Compiler\MapperPass;

/**
 * Bundle class
 */
class SymfonyCmfRoutingExtraBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ChainRouterPass());
        $container->addCompilerPass(new MapperPass());
    }
}
