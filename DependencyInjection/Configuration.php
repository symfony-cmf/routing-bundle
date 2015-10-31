<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
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
                        ->booleanNode('replace_symfony_router')->defaultTrue()->end()
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
                        ->scalarNode('route_collection_limit')->defaultValue(0)->end()
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
                            ->useAttributeAsKey('class')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('persistence')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('resource')
                                    ->canBeEnabled()
                                    ->children()
                                        ->scalarNode('discovery_id')->defaultValue('cmf_resource.discovery')->end()
                                    ->end()
                                ->end()
                                ->arrayNode('phpcr')
                                    ->addDefaultsIfNotSet()
                                    ->canBeEnabled()
                                    ->fixXmlConfig('route_basepath')
                                    ->validate()
                                        ->ifTrue(function ($v) { isset($v['route_basepath']) && isset($v['route_basepaths']); })
                                        ->thenInvalid('Found values for both "route_basepath" and "route_basepaths", use "route_basepaths" instead.')
                                    ->end()
                                    ->beforeNormalization()
                                        ->ifTrue(function ($v) { return isset($v['route_basepath']); })
                                        ->then(function ($v) {
                                            @trigger_error('The route_basepath setting is deprecated as of version 1.4 and will be removed in 2.0. Use route_basepaths instead.', E_USER_DEPRECATED);

                                            return $v;
                                        })
                                    ->end()
                                    ->children()
                                        ->scalarNode('manager_name')->defaultNull()->end()
                                        ->arrayNode('route_basepaths')
                                            ->beforeNormalization()
                                                ->ifString()
                                                ->then(function ($v) { return array($v); })
                                            ->end()
                                            ->prototype('scalar')->end()
                                            ->defaultValue(array('/cms/routes'))
                                        ->end()
                                        ->scalarNode('admin_basepath')->defaultNull()->end()
                                        ->scalarNode('content_basepath')->defaultValue('/cms/content')->end()
                                        ->enumNode('use_sonata_admin')
                                            ->beforeNormalization()
                                                ->ifString()
                                                ->then(function ($v) {
                                                    switch ($v) {
                                                        case 'true':
                                                            return true;

                                                        case 'false':
                                                            return false;

                                                        default:
                                                            return $v;
                                                    }
                                                })
                                            ->end()
                                            ->values(array(true, false, 'auto'))
                                            ->defaultValue('auto')
                                        ->end()
                                        ->booleanNode('enable_initializer')->defaultTrue()->end()
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
                        ->integerNode('limit_candidates')->defaultValue(20)->end()
                        ->booleanNode('match_implicit_locale')->defaultValue(true)->end()
                        ->booleanNode('auto_locale_pattern')->defaultValue(false)->end()
                        ->scalarNode('url_generator')
                            ->defaultValue('cmf_routing.generator')
                            ->info('URL generator service ID')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
