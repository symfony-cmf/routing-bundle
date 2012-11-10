<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * This compiler pass adds additional controller mappers 
 * to the dynamic router.
 *
 * @author Daniel Leech <dan.t.leech@gmail.com>
 */
class MapperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('symfony_cmf_routing_extra.dynamic_router')) {
            return;
        }

        $router = $container->getDefinition('symfony_cmf_routing_extra.dynamic_router');

        foreach ($container->findTaggedServiceIds('dynamic_router_controller_mapper') as $id => $attributes) {
            $router->addMethodCall('addControllerMapper', array(new Reference($id)));
        }
    }
}
