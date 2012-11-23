<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection\Compiler;

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
        if (true === $container->getParameter('symfony_cmf_routing_extra.replace_symfony_router')) {
            $container->setAlias('router', 'symfony_cmf_routing_extra.router');
        }

    }
}
