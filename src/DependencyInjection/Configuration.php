<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle.
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
        $treeBuilder = new TreeBuilder('cmf_routing');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $root = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $root = $treeBuilder->root('cmf_routing');
        }

        $this->addChainSection($root);
        $this->addDynamicSection($root);

        return $treeBuilder;
    }

    private function addChainSection(ArrayNodeDefinition $root)
    {
        $root
            ->children()
                ->arrayNode('chain')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('router_by_id', 'routers_by_id')
                    ->children()
                        ->arrayNode('routers_by_id')
                            ->defaultValue(['router.default' => 100])
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                        ->end() // routers_by_id
                        ->booleanNode('replace_symfony_router')->defaultTrue()->end()
                    ->end()
                ->end()// chain
            ->end()
        ;
    }

    private function addDynamicSection(ArrayNodeDefinition $root)
    {
        $root
            ->children()
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
                        ->end() // controllers_by_type
                        ->arrayNode('controllers_by_class')
                            ->useAttributeAsKey('class')
                            ->prototype('scalar')->end()
                        ->end() // controllers_by_class
                        ->arrayNode('templates_by_class')
                            ->useAttributeAsKey('class')
                            ->prototype('scalar')->end()
                        ->end() // templates_by_class
                        ->arrayNode('persistence')
                            ->addDefaultsIfNotSet()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return \count(array_filter($v, function ($persistence) {
                                        return $persistence['enabled'];
                                    })) > 1;
                                })
                                ->thenInvalid('Only one persistence layer can be enabled at the same time.')
                            ->end()
                            ->children()
                                ->arrayNode('phpcr')
                                    ->addDefaultsIfNotSet()
                                    ->canBeEnabled()
                                    ->fixXmlConfig('route_basepath')
                                    ->children()
                                        ->scalarNode('manager_name')->defaultNull()->end()
                                        ->arrayNode('route_basepaths')
                                            ->beforeNormalization()
                                                ->ifString()
                                                ->then(function ($v) {
                                                    return [$v];
                                                })
                                            ->end()
                                            ->prototype('scalar')->end()
                                            ->defaultValue(['/cms/routes'])
                                        ->end() // route_basepaths
                                        ->booleanNode('enable_initializer')
                                            ->defaultValue(true)
                                        ->end()
                                    ->end()
                                ->end() // phpcr
                                ->arrayNode('orm')
                                    ->addDefaultsIfNotSet()
                                    ->canBeEnabled()
                                    ->children()
                                        ->scalarNode('manager_name')->defaultNull()->end()
                                        ->scalarNode('route_class')->defaultValue(Route::class)->end()
                                    ->end()
                                ->end() // orm
                            ->end()
                        ->end() // persistence
                        ->scalarNode('uri_filter_regexp')->defaultValue('')->end()
                        ->scalarNode('route_provider_service_id')->end()
                        ->arrayNode('route_filters_by_id')
                            ->canBeUnset()
                            ->defaultValue([])
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                        ->end() // route_filters_by_id
                        ->scalarNode('content_repository_service_id')->end()
                        ->arrayNode('locales')
                            ->prototype('scalar')->end()
                        ->end() // locales
                        ->integerNode('limit_candidates')->defaultValue(20)->end()
                        ->booleanNode('match_implicit_locale')->defaultValue(true)->end()
                        ->booleanNode('auto_locale_pattern')->defaultValue(false)->end()
                        ->scalarNode('url_generator')
                            ->defaultValue('cmf_routing.generator')
                            ->info('URL generator service ID')
                        ->end() // url_generator
                    ->end()
                ->end() // dynamic
            ->end()
        ;
    }
}
