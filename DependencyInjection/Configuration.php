<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
* This class contains the configuration information for the bundle
*
* This information is solely responsible for how the different configuration
* sections are normalized, and merged.
*
* @author David Buchmann
*/
class Configuration implements ConfigurationInterface
{
    /**
     * Returns the config tree builder.
     *
     * @return \Symfony\Component\DependencyInjection\Configuration\NodeInterface
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('symfony_cmf_routing_extra')
            ->children()
                ->arrayNode('chain')
                    ->children()
                        ->arrayNode('routers_by_id')
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('replace_symfony_router')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('dynamic')
                    ->children()
                        ->scalarNode('enabled')->defaultNull()->end()
                        ->scalarNode('generic_controller')->defaultValue('symfony_cmf_content.controller:indexAction')->end()
                        ->arrayNode('controllers_by_alias')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')
                        ->end()->end()
                        ->arrayNode('controllers_by_class')
                            ->useAttributeAsKey('class')
                            ->prototype('scalar')
                            /* why does this not work?
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('Symfony\Cmf\Component\Routing\RedirectRouteInterface')
                                        ->defaultValue('symfony_cmf_routing_extra.redirect_controller:redirectAction')
                                    ->end()
                                ->end()
                             */
                        ->end()->end()
                        ->arrayNode('templates_by_class')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')
                        ->end()->end()
                        ->scalarNode('manager_registry')->defaultValue('doctrine_phpcr')->end()
                        ->scalarNode('manager_name')->defaultValue('default')->end()
                        ->scalarNode('routing_repositoryroot')->defaultValue('/cms/routes')->end()
                    ->end()
                ->end()
                ->enumNode('use_sonata_admin')
                    ->values(array(true, false, 'auto'))
                    ->defaultValue('auto')
                ->end()
                ->scalarNode('content_basepath')->defaultNull()->end()
                // TODO: fix widget to show root node when root is selectable, then use /cms/routing here
                // resp. use routing_repositoryroot for both
                ->scalarNode('route_basepath')->defaultValue('/cms')->end()
        ->end();

        return $treeBuilder;
    }
}
