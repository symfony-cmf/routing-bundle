<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * A CompilerPass which takes the standard router and replaces it.
 *
 *
 * @author Henrik Bjornskov <henrik@bjrnskov.dk>
 * @author Magnus Nordlander <magnus@e-butik.se>
 */
class ChainRouterPass implements CompilerPassInterface
{
    /**
     * Adds any tagged subrouters to the chain router, as well as router.real if it exists.
     * Router.real is added at priority 100.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('symfony_cmf_routing_extra.router')) {
            return;
        }

        $router = $container->getDefinition('symfony_cmf_routing_extra.router');

        foreach ($container->findTaggedServiceIds('router') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? (integer) $attributes[0]['priority'] : 0;
            $router->addMethodCall('add', array(new Reference($id), $priority));
        }
    }
}

