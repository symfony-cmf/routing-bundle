<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
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
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('cmf_routing')
            ->children()
                ->arrayNode('chain')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('router_by_id', 'routers_by_id')
                    ->children()
                        ->arrayNode('routers_by_id')
                            ->defaultValue(array('router.default' => 100))
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('replace_symfony_router')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('dynamic')
                    ->fixXmlConfig('controller_by_type', 'controllers_by_type')
                    ->fixXmlConfig('controller_by_class', 'controllers_by_class')
                    ->fixXmlConfig('template_by_class', 'templates_by_class')
                    ->fixXmlConfig('route_filter_by_id', 'route_filters_by_id')
                    ->fixXmlConfig('locale')
                    ->children()
                        ->scalarNode('enabled')->defaultNull()->end()
                        ->scalarNode('generic_controller')->defaultValue('cmf_content.controller:indexAction')->end()
                        ->arrayNode('controllers_by_type')
                            ->useAttributeAsKey('type')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('controllers_by_class')
                            ->useAttributeAsKey('class')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('templates_by_class')
                            ->useAttributeAsKey('alias')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('manager_registry')->defaultValue('doctrine_phpcr')->end()
                        ->scalarNode('manager_name')->defaultValue('default')->end()
                        ->scalarNode('uri_filter_regexp')->defaultValue('')->end()
                        ->scalarNode('route_provider_service_id')->defaultValue('cmf_routing.default_route_provider')->end()
                        ->arrayNode('route_filters_by_id')
                            ->canBeUnset()
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('content_repository_service_id')->defaultValue('cmf_routing.default_content_repository')->end()
                        ->scalarNode('routing_repositoryroot')->defaultValue('/cms/routes')->end()
                        ->arrayNode('locales')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->enumNode('use_sonata_admin')
                    ->values(array(true, false, 'auto'))
                    ->defaultValue('auto')
                ->end()
                ->scalarNode('content_basepath')->defaultValue('/cms/content')->end()
                // TODO: fix widget to show root node when root is selectable, then use routing_repositoryroot for both
                ->scalarNode('route_basepath')->defaultValue('/cms')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
