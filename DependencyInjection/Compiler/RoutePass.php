<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * A CompilerPass which registers the mapping file for the core Route class
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class RoutePass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cmf_routing.manager_registry')) {
            return;
        }

        $managerRegistry = $container->getDefinition('cmf_routing.manager_registry');
        $managerName = $managerRegistry->getArgument(0);
        $chainDriverDefService = sprintf('doctrine_phpcr.odm.%s_metadata_driver', $managerName);
        if (!$container->hasDefinition($chainDriverDefService)) {
            return;
        }

        $mappingService = 'cmf_routing.phpcr_mapping.core_route';
        $arguments = array('Symfony\Component\Routing' => realpath(__DIR__.'/../../Resources/config/doctrine'));
        $mappingDriverDef = new Definition('Doctrine\ODM\PHPCR\Mapping\Driver\XmlDriver', $arguments);
        $container->setDefinition($mappingService, $mappingDriverDef);

        $chainDriverDef = $container->getDefinition($chainDriverDefService);
        $chainDriverDef->addMethodCall('addDriver', array(new Reference($mappingService), 'Symfony\Component\Routing'));
    }
}
