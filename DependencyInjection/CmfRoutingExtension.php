<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * @author Philippo de Santis
 * @author David Buchmann
 */
class CmfRoutingExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('metadata.xml');

        if ($config['dynamic']['enabled']) {
            // load this even if no explicit enabled value but some configuration
            $this->setupDynamicRouter($config['dynamic'], $container, $loader);
            $this->setupMetadataDrivers($config['dynamic'], $container);
        }

        /* set up the chain router */
        $loader->load('routing-chain.xml');
        $container->setParameter($this->getAlias() . '.replace_symfony_router', $config['chain']['replace_symfony_router']);

        // add the routers defined in the configuration mapping
        $router = $container->getDefinition($this->getAlias() . '.router');
        foreach ($config['chain']['routers_by_id'] as $id => $priority) {
            $router->addMethodCall('add', array(new Reference($id), trim($priority)));
        }

        $this->setupFormTypes($config, $container, $loader);
    }

    public function setupMetadataDrivers($config, ContainerBuilder $container)
    {
        $configDriver = $container->getDefinition($this->getAlias() . '.metadata.driver.configuration');
        $annotationDriver = $container->getDefinition($this->getAlias() . '.metadata.driver.annotation');

        $mapping = array();

        $controller = $container->getParameter($this->getAlias() . '.generic_controller');;

        // configure annotation driver
        $annotationDriver->addMethodCall('setGenericController', array($controller));

        $mappings = array();

        // configure configuration driver
        if (!empty($config['controllers_by_class'])) {
            foreach ($config['controllers_by_class'] as $classFqn => $controller) {
                if (!isset($mappings[$classFqn])) {
                    $mappings[$classFqn] = array();
                }

                $mappings[$classFqn]['controller'] = $controller;
            }
        }

        if (!empty($config['templates_by_class'])) {
            foreach ($config['templates_by_class'] as $classFqn => $template) {
                if (!isset($mappings[$classFqn])) {
                    $mappings[$classFqn] = array();
                }

                $mappings[$classFqn]['template'] = $template;
            }
        }

        foreach ($mappings as $classFqn => &$mapping) {
            if (!isset($mapping['controller'])) {
                $mapping['controller'] = $container->getParameter(
                    $this->getAlias() . '.generic_controller'
                );
            }

            $configDriver->addMethodCall('registerMapping', array($classFqn, $mapping));
        }
    }

    public function setupFormTypes(array $config, ContainerBuilder $container, LoaderInterface $loader)
    {
        $loader->load('form-type.xml');

        if (isset($config['dynamic'])) {
            $routeTypeTypeDefinition = $container->getDefinition($this->getAlias() . '.route_type_form_type');

            foreach (array_keys($config['dynamic']['controllers_by_type']) as $routeType) {
                $routeTypeTypeDefinition->addMethodCall('addRouteType', array($routeType));
            }
        }
    }

    /**
     * Set up the DynamicRouter - only to be called if enabled is set to true
     *
     * @param array            $config    the compiled configuration for the dynamic router
     * @param ContainerBuilder $container the container builder
     * @param LoaderInterface  $loader    the configuration loader
     */
    private function setupDynamicRouter(array $config, ContainerBuilder $container, LoaderInterface $loader)
    {
        // strip whitespace (XML support)
        foreach (array('controllers_by_type', 'route_filters_by_id') as $option) {
            $config[$option] = array_map(function ($value) {
                return trim($value);
            }, $config[$option]);
        }

        $defaultController = $config['default_controller'];
        if (null === $defaultController) {
            $defaultController = $config['generic_controller'];
        }

        $container->setParameter($this->getAlias() . '.default_controller', $defaultController);
        $container->setParameter($this->getAlias() . '.generic_controller', $config['generic_controller']);
        $container->setParameter($this->getAlias() . '.controllers_by_type', $config['controllers_by_type']);
        $container->setParameter($this->getAlias() . '.uri_filter_regexp', $config['uri_filter_regexp']);
        $container->setParameter($this->getAlias() . '.route_collection_limit', $config['route_collection_limit']);

        $locales = empty($config['locales']) ? array() : $config['locales'];
        $container->setParameter($this->getAlias() . '.dynamic.locales', $locales);
        if (count($locales) === 0 && $config['auto_locale_pattern']) {
            throw new InvalidConfigurationException('It makes no sense to activate auto_locale_pattern when no locales are configured.');
        }
        $container->setParameter($this->getAlias() . '.dynamic.auto_locale_pattern', $config['auto_locale_pattern']);

        $container->setParameter($this->getAlias() . '.dynamic.limit_candidates', $config['limit_candidates']);

        $loader->load('routing-dynamic.xml');

        $hasProvider = false;
        $hasContentRepository = false;
        if ($config['persistence']['phpcr']['enabled'] && $config['persistence']['orm']['enabled']) {
            throw new InvalidConfigurationException('You can only enable either phpcr or orm, not both.');
        }

        if ($config['persistence']['phpcr']['enabled']) {
            $this->loadPhpcrProvider($config['persistence']['phpcr'], $loader, $container, $locales, $config['match_implicit_locale']);
            $hasProvider = true;
            $hasContentRepository = true;
        }

        if ($config['persistence']['orm']['enabled']) {
            $this->loadOrmProvider($config['persistence']['orm'], $loader, $container, $locales, $config['match_implicit_locale']);
            $hasProvider = true;
        }

        if (isset($config['route_provider_service_id'])) {
            $container->setAlias($this->getAlias() . '.route_provider', $config['route_provider_service_id']);
            $hasProvider = true;
        }
        if (!$hasProvider) {
            throw new InvalidConfigurationException('When the dynamic router is enabled, you need to either enable one of the persistence layers or set the cmf_routing.dynamic.route_provider_service_id option');
        }
        if (isset($config['content_repository_service_id'])) {
            $container->setAlias($this->getAlias() . '.content_repository', $config['content_repository_service_id']);
            $hasContentRepository = true;
        }
        // content repository is optional
        if ($hasContentRepository) {
            $generator = $container->getDefinition($this->getAlias() . '.generator');
            $generator->addMethodCall('setContentRepository', array(
                new Reference($this->getAlias() . '.content_repository'),
            ));
        }

        $dynamic = $container->getDefinition($this->getAlias() . '.dynamic_router');

        // if any mappings are defined, set the respective route enhancer
        if (!empty($config['controllers_by_type'])) {
            $dynamic->addMethodCall(
                'addRouteEnhancer',
                array(
                    new Reference($this->getAlias() . '.enhancer.controllers_by_type'),
                    60
                )
            );
        }

        $dynamic->addMethodCall(
            'addRouteEnhancer',
            array(
                new Reference($this->getAlias() . '.enhancer.controllers_by_class'),
                50
            )
        );

        $dynamic->addMethodCall(
            'addRouteEnhancer',
            array(
                new Reference($this->getAlias() . '.enhancer.templates_by_class'),
                40
            )
        );

        if (!empty($config['generic_controller']) && $config['generic_controller'] !== $defaultController) {
            $dynamic->addMethodCall(
                'addRouteEnhancer',
                array(
                    new Reference($this->getAlias() . '.enhancer.explicit_template'),
                    10
                )
            );
        }
        if ($defaultController) {
            $dynamic->addMethodCall(
                'addRouteEnhancer',
                array(
                    new Reference($this->getAlias() . '.enhancer.default_controller'),
                    -100
                )
            );
        }

        if (!empty($config['route_filters_by_id'])) {
            $matcher = $container->getDefinition($this->getAlias() . '.nested_matcher');
            foreach ($config['route_filters_by_id'] as $id => $priority) {
                $matcher->addMethodCall('addRouteFilter', array(new Reference($id), $priority));
            }
        }
    }

    public function loadPhpcrProvider($config, XmlFileLoader $loader, ContainerBuilder $container, $locales, $matchImplicitLocale)
    {
        $loader->load('provider-phpcr.xml');

        $container->setParameter($this->getAlias() . '.backend_type_phpcr', true);

        $container->setParameter(
            $this->getAlias() . '.dynamic.persistence.phpcr.route_basepaths',
            $config['route_basepaths']
        );
        $container->setParameter(
            $this->getAlias() . '.dynamic.persistence.phpcr.content_basepath',
            $config['content_basepath']
        );

        $container->setParameter(
            $this->getAlias() . '.dynamic.persistence.phpcr.manager_name',
            $config['manager_name']
        );

        $container->setAlias(
            $this->getAlias() . '.route_provider',
            $this->getAlias() . '.phpcr_route_provider'
        );
        $container->setAlias(
            $this->getAlias() . '.content_repository',
            $this->getAlias() . '.phpcr_content_repository'
        );

        if (!$locales) {
            $container->removeDefinition($this->getAlias() . '.phpcrodm_route_locale_listener');
        } elseif (!$matchImplicitLocale) {
            // remove all but the prefixes configuration from the service definition.
            $definition = $container->getDefinition($this->getAlias() . '.phpcr_candidates_prefix');
            $definition->setArguments(array($definition->getArgument(0)));
        }

        if ($config['use_sonata_admin']) {
            $this->loadSonataPhpcrAdmin($config, $loader, $container);
        }
    }

    public function loadSonataPhpcrAdmin($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_sonata_admin'] && !isset($bundles['SonataDoctrinePHPCRAdminBundle'])) {
            return;
        }

        $basePath = empty($config['admin_basepath']) ? reset($config['route_basepaths']) : $config['admin_basepath'];
        $container->setParameter($this->getAlias() . '.dynamic.persistence.phpcr.admin_basepath', $basePath);

        $loader->load('admin-phpcr.xml');
    }

    public function loadOrmProvider($config, XmlFileLoader $loader, ContainerBuilder $container, $matchImplicitLocale)
    {
        $container->setParameter($this->getAlias() . '.dynamic.persistence.orm.manager_name', $config['manager_name']);
        $container->setParameter($this->getAlias() . '.backend_type_orm', true);
        $loader->load('provider-orm.xml');

        if (!$matchImplicitLocale) {
            // remove the locales argument from the candidates
            $definition = $container->getDefinition($this->getAlias() . '.orm_candidates');
            $definition->setArguments(array());
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://cmf.symfony.com/schema/dic/routing';
    }
}
