<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


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
                    ->addDefaultsIfNotSet()
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('generic_controller')->defaultNull()->end()
                        ->scalarNode('default_controller')->defaultNull()->end()
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
                        ->arrayNode('persistence')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('phpcr')
                                    ->addDefaultsIfNotSet()
                                    ->canBeEnabled()
                                    ->children()
                                        ->scalarNode('manager_name')->defaultNull()->end()
                                        ->scalarNode('route_basepath')->defaultValue('/cms/routes')->end()
                                        ->scalarNode('content_basepath')->defaultValue('/cms/content')->end()
                                        ->enumNode('use_sonata_admin')
                                            ->values(array(true, false, 'auto'))
                                            ->defaultValue('auto')
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('orm')
                                    ->addDefaultsIfNotSet()
                                    ->canBeEnabled()
                                    ->children()
                                        ->scalarNode('manager_name')->defaultNull()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('uri_filter_regexp')->defaultValue('')->end()
                        ->scalarNode('route_provider_service_id')->end()
                        ->arrayNode('route_filters_by_id')
                            ->canBeUnset()
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('content_repository_service_id')->end()
                        ->arrayNode('locales')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
