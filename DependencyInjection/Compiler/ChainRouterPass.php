<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler;

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
        if (!$container->hasDefinition('cmf_routing.router')) {
            return;
        }

        $router = $container->getDefinition('cmf_routing.router');

        foreach ($container->findTaggedServiceIds('router') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? (integer) $attributes[0]['priority'] : 0;
            $router->addMethodCall('add', array(new Reference($id), $priority));
        }
    }
}
