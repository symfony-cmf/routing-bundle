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

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * @author Philippo de Santis
 * @author David Buchmann
 * @author Wouter de Jong <wouter@wouterj.nl>
 */
class CmfRoutingExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if ($this->isConfigEnabled($container, $config['dynamic'])) {
            $this->setupDynamicRouter($config['dynamic'], $container, $loader);
        }

        $this->setupChainRouter($config, $container, $loader);
        $this->setupFormTypes($config, $container, $loader);

        $loader->load('validators.xml');
    }

    private function setupChainRouter(array $config, ContainerBuilder $container, LoaderInterface $loader)
    {
        $loader->load('routing-chain.xml');

        $container->setParameter('cmf_routing.replace_symfony_router', $config['chain']['replace_symfony_router']);

        // add the routers defined in the configuration mapping
        $router = $container->getDefinition('cmf_routing.router');
        foreach ($config['chain']['routers_by_id'] as $id => $priority) {
            $router->addMethodCall('add', array(new Reference($id), trim($priority)));
        }
    }

    private function setupFormTypes(array $config, ContainerBuilder $container, LoaderInterface $loader)
    {
        $loader->load('form-type.xml');

        if (isset($config['dynamic'])) {
            $routeTypeTypeDefinition = $container->getDefinition('cmf_routing.route_type_form_type');

            foreach (array_keys($config['dynamic']['controllers_by_type']) as $routeType) {
                $routeTypeTypeDefinition->addMethodCall('addRouteType', array($routeType));
            }
        }
    }

    /**
     * Set up the DynamicRouter - only to be called if enabled is set to true.
     *
     * @param array            $config    the compiled configuration for the dynamic router
     * @param ContainerBuilder $container the container builder
     * @param LoaderInterface  $loader    the configuration loader
     */
    private function setupDynamicRouter(array $config, ContainerBuilder $container, LoaderInterface $loader)
    {
        $loader->load('routing-dynamic.xml');

        // strip whitespace (XML support)
        foreach (array('controllers_by_type', 'controllers_by_class', 'templates_by_class', 'route_filters_by_id') as $option) {
            $config[$option] = array_map('trim', $config[$option]);
        }

        $defaultController = $config['default_controller'];
        if (null === $defaultController) {
            $defaultController = $config['generic_controller'];
        }
        $container->setParameter('cmf_routing.default_controller', $defaultController);

        $locales = $config['locales'];
        if (0 === count($locales) && $config['auto_locale_pattern']) {
            throw new InvalidConfigurationException('It makes no sense to activate auto_locale_pattern when no locales are configured.');
        }

        $this->configureParameters($container, $config, array(
            'generic_controller' => 'generic_controller',
            'controllers_by_type' => 'controllers_by_type',
            'controllers_by_class' => 'controllers_by_class',
            'templates_by_class' => 'templates_by_class',
            'uri_filter_regexp' => 'uri_filter_regexp',
            'route_collection_limit' => 'route_collection_limit',
            'limit_candidates' => 'dynamic.limit_candidates',
            'locales' => 'dynamic.locales',
            'auto_locale_pattern' => 'dynamic.auto_locale_pattern',
        ));

        $hasProvider = false;
        $hasContentRepository = false;
        if ($config['persistence']['phpcr']['enabled']) {
            $this->loadPhpcrProvider($config['persistence']['phpcr'], $loader, $container, $locales, $config['match_implicit_locale']);
            $hasProvider = $hasContentRepository = true;
        }

        if ($config['persistence']['orm']['enabled']) {
            $this->loadOrmProvider($config['persistence']['orm'], $loader, $container, $locales, $config['match_implicit_locale']);
            $hasProvider = $hasContentRepository = true;
        }

        if (isset($config['route_provider_service_id'])) {
            $container->setAlias('cmf_routing.route_provider', $config['route_provider_service_id']);
            $hasProvider = true;
        }

        if (!$hasProvider) {
            throw new InvalidConfigurationException('When the dynamic router is enabled, you need to either enable one of the persistence layers or set the cmf_routing.dynamic.route_provider_service_id option');
        }

        if (isset($config['content_repository_service_id'])) {
            $container->setAlias('cmf_routing.content_repository', $config['content_repository_service_id']);
            $hasContentRepository = true;
        }

        // content repository is optional
        if ($hasContentRepository) {
            $generator = $container->getDefinition('cmf_routing.generator');
            $generator->addMethodCall('setContentRepository', array(new Reference('cmf_routing.content_repository')));
            $container->getDefinition('cmf_routing.enhancer.content_repository')
                      ->addTag('dynamic_router_route_enhancer', array('priority' => 100));
        }

        $dynamic = $container->getDefinition('cmf_routing.dynamic_router');

        // if any mappings are defined, set the respective route enhancer
        if (count($config['controllers_by_type']) > 0) {
            $container->getDefinition('cmf_routing.enhancer.controllers_by_type')
                ->addTag('dynamic_router_route_enhancer', array('priority' => 60));
        }

        if (count($config['controllers_by_class']) > 0) {
            $container->getDefinition('cmf_routing.enhancer.controllers_by_class')
                      ->addTag('dynamic_router_route_enhancer', array('priority' => 50));
        }

        if (count($config['templates_by_class']) > 0) {
            $container->getDefinition('cmf_routing.enhancer.templates_by_class')
                      ->addTag('dynamic_router_route_enhancer', array('priority' => 40));

            /*
             * The CoreBundle prepends the controller from ContentBundle if the
             * ContentBundle is present in the project.
             * If you are sure you do not need a generic controller, set the field
             * to false to disable this check explicitly. But you would need
             * something else like the default_controller to set the controller,
             * as no controller will be set here.
             */
            if (null === $config['generic_controller']) {
                throw new InvalidConfigurationException('If you want to configure templates_by_class, you need to configure the generic_controller option.');
            }

            // if the content class defines the template, we also need to make sure we use the generic controller for those routes
            $controllerForTemplates = array();
            foreach ($config['templates_by_class'] as $key => $value) {
                $controllerForTemplates[$key] = $config['generic_controller'];
            }

            $definition = $container->getDefinition('cmf_routing.enhancer.controller_for_templates_by_class');
            $definition->replaceArgument(2, $controllerForTemplates);

            $container->getDefinition('cmf_routing.enhancer.controller_for_templates_by_class')
                      ->addTag('dynamic_router_route_enhancer', array('priority' => 30));
        }

        if (null !== $config['generic_controller'] && $defaultController !== $config['generic_controller']) {
            $container->getDefinition('cmf_routing.enhancer.explicit_template')
                      ->addTag('dynamic_router_route_enhancer', array('priority' => 10));
        }

        if (null !== $defaultController) {
            $container->getDefinition('cmf_routing.enhancer.default_controller')
                      ->addTag('dynamic_router_route_enhancer', array('priority' => -100));
        }

        if (count($config['route_filters_by_id']) > 0) {
            $matcher = $container->getDefinition('cmf_routing.nested_matcher');

            foreach ($config['route_filters_by_id'] as $id => $priority) {
                $matcher->addMethodCall('addRouteFilter', array(new Reference($id), $priority));
            }
        }

        $dynamic->replaceArgument(2, new Reference($config['url_generator']));
    }

    private function loadPhpcrProvider($config, XmlFileLoader $loader, ContainerBuilder $container, array $locales, $matchImplicitLocale)
    {
        $loader->load('provider-phpcr.xml');

        $container->setParameter('cmf_routing.backend_type_phpcr', true);
        $container->setParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths', array_values(array_unique($config['route_basepaths'])));

        /*
         * @deprecated The cmf_routing.dynamic.persistence.phpcr.route_basepath parameter is deprecated as of version 1.4 and will be removed in 2.0. Use the cmf_routing.dynamic.persistence.phpcr.route_basepaths parameter instead.
         */
        $container->setParameter('cmf_routing.dynamic.persistence.phpcr.route_basepath', reset($config['route_basepaths']));
        $container->setParameter('cmf_routing.dynamic.persistence.phpcr.content_basepath', $config['content_basepath']);
        $container->setParameter('cmf_routing.dynamic.persistence.phpcr.manager_name', $config['manager_name']);

        if (0 === count($locales)) {
            $container->removeDefinition('cmf_routing.phpcrodm_route_locale_listener');
        } elseif (!$matchImplicitLocale) {
            // remove all but the prefixes configuration from the service definition.
            $definition = $container->getDefinition('cmf_routing.phpcr_candidates_prefix');
            $definition->setArguments(array($definition->getArgument(0)));
        }

        if ($config['use_sonata_admin']) {
            $this->loadSonataPhpcrAdmin($config, $loader, $container);
        }

        if ($config['enable_initializer']) {
            $this->loadInitializer($config, $loader, $container);
        }
    }

    private function loadSonataPhpcrAdmin($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_sonata_admin'] && !isset($bundles['SonataDoctrinePHPCRAdminBundle'])) {
            return;
        }

        $loader->load('admin-phpcr.xml');

        $basePath = $config['admin_basepath'] ?: reset($config['route_basepaths']);
        $container->setParameter('cmf_routing.dynamic.persistence.phpcr.admin_basepath', $basePath);
    }

    /**
     * @param array            $config
     * @param XmlFileLoader    $loader
     * @param ContainerBuilder $container
     */
    private function loadInitializer($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $adminBasepathParameter = $this->getAlias().'.dynamic.persistence.phpcr.admin_basepath';

        $initializedBasepaths = array();
        if ($container->hasParameter($adminBasepathParameter)) {
            $initializedBasepaths = array($container->getParameter($adminBasepathParameter));
        }

        if ('auto' === $config['enable_initializer'] && empty($initializedBasepaths)) {
            return;
        }

        if (true === $config['enable_initializer'] && empty($initializedBasepaths)) {
            $initializedBasepaths = $container->getParameter($this->getAlias().'.dynamic.persistence.phpcr.route_basepaths');
        }

        $container->setParameter(
            $this->getAlias().'.dynamic.persistence.phpcr.initialized_basepaths',
            $initializedBasepaths
        );

        $loader->load('initializer-phpcr.xml');
    }

    private function loadOrmProvider($config, XmlFileLoader $loader, ContainerBuilder $container, $matchImplicitLocale)
    {
        $loader->load('provider-orm.xml');

        $container->setParameter('cmf_routing.backend_type_orm', true);
        $container->setParameter('cmf_routing.dynamic.persistence.orm.manager_name', $config['manager_name']);

        if (!$matchImplicitLocale) {
            // remove the locales argument from the candidates
            $container->getDefinition('cmf_routing.orm_candidates')->setArguments(array());
        }
    }

    /**
     * @param ContainerBuilder $container          The container builder
     * @param array            $config             The config array
     * @param array            $settingToParameter An array with setting to parameter mappings (key = setting, value = parameter name without alias prefix)
     */
    private function configureParameters(ContainerBuilder $container, array $config, array $settingToParameter)
    {
        foreach ($settingToParameter as $setting => $parameter) {
            $container->setParameter('cmf_routing.'.$parameter, $config[$setting]);
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
