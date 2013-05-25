<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Changes the Router implementation.
 *
 */
class SetRouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {

        // only replace the default router by overwriting the 'router' alias if config tells us to
        if (true === $container->getParameter('cmf_routing.replace_symfony_router')) {
            $container->setAlias('router', 'cmf_routing.router');
        }

    }
}
