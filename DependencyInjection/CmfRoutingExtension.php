<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

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
        $processor = new Processor();
        $configuration = new Configuration();
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $config = $processor->processConfiguration($configuration, $configs);

        if (!empty($config['dynamic'])) {
            // load this even if no explicit enabled value but some configuration
            $this->setupDynamicRouter($config['dynamic'], $container, $loader);
        }

        /* set up the chain router */
        $loader->load('chain_routing.xml');
        $container->setParameter($this->getAlias() . '.replace_symfony_router', $config['chain']['replace_symfony_router']);

        // add the routers defined in the configuration mapping
        $router = $container->getDefinition($this->getAlias() . '.router');
        foreach ($config['chain']['routers_by_id'] as $id => $priority) {
            $router->addMethodCall('add', array(new Reference($id), $priority));
        }

        $this->setupFormTypes($config, $container, $loader);
    }

    public function setupFormTypes(array $config, ContainerBuilder $container, LoaderInterface $loader)
    {
        $loader->load('form_type.xml');

        if (isset($config['dynamic'])) {
            $routeTypeTypeDefinition = $container->getDefinition('cmf_routing.route_type_form_type');

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
        if (isset($config['enabled']) && false == $config['enabled']) {
            // prevent wtf if somebody is playing around with configs
            return;
        }
        $container->setParameter($this->getAlias() . '.generic_controller', $config['generic_controller']);
        $container->setParameter($this->getAlias() . '.controllers_by_type', $config['controllers_by_type']);
        $container->setParameter($this->getAlias() . '.controllers_by_class', $config['controllers_by_class']);
        $container->setParameter($this->getAlias() . '.templates_by_class', $config['templates_by_class']);
        // if the content class defines the template, we also need to make sure we use the generic controller for those routes
        $controllerForTemplates = array();
        foreach ($config['templates_by_class'] as $key => $value) {
            $controllerForTemplates[$key] = $config['generic_controller'];
        }
        $container->setParameter($this->getAlias() . '.defined_templates_class', $controllerForTemplates);
        $container->setParameter($this->getAlias() . '.uri_filter_regexp', $config['uri_filter_regexp']);

        $loader->load('dynamic_routing.xml');

        if (isset($config['phpcr_provider'])) {
            $this->loadPhpcrProvider($config['phpcr_provider'], $loader, $container);
        }

        if (isset($config['route_provider_service_id'])) {
            $container->setAlias('cmf_routing.route_provider', $config['route_provider_service_id']);
        }
        if (isset($config['content_repository_service_id'])) {
            $container->setAlias('cmf_routing.content_repository', $config['content_repository_service_id']);
        }

        $dynamic = $container->getDefinition($this->getAlias().'.dynamic_router');

        // if any mappings are defined, set the respective route enhancer
        if (!empty($config['generic_controller'])) {
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_explicit_template')));
        }
        if (!empty($config['controllers_by_type'])) {
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_controllers_by_type')));
        }
        if (!empty($config['controllers_by_class'])) {
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_controllers_by_class')));
        }
        if (!empty($config['generic_controller']) && !empty($config['templates_by_class'])) {
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_controller_for_templates_by_class')));
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_templates_by_class')));
        }
        if (!empty($config['route_filters_by_id'])) {
            $matcher = $container->getDefinition('cmf_routing.nested_matcher');
            foreach ($config['route_filters_by_id'] as $id => $priority) {
                $matcher->addMethodCall('addRouteFilter', array(new Reference($id), $priority));
            }
        }
    }

    public function loadPhpcrProvider($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $loader->load('provider_phpcr.xml');

        $container->setParameter($this->getAlias() . '.backend_type_phpcr', true);

        $container->setParameter($this->getAlias() . '.phpcr_provider.route_basepath', $config['route_basepath']);
        $container->setParameter($this->getAlias() . '.phpcr_provider.content_basepath', $config['content_basepath']);

        $routeProvider = $container->getDefinition($this->getAlias() . '.phpcr_route_provider');
        $routeProvider->replaceArgument(0, new Reference($config['manager_registry']));
        $contentRepository = $container->getDefinition($this->getAlias() . '.phpcr_content_repository');
        $contentRepository->replaceArgument(0, new Reference($config['manager_registry']));
        $container->setParameter($this->getAlias() . '.manager_name', $config['manager_name']);

        $container->setAlias($this->getAlias() . '.route_provider', $this->getAlias() . '.phpcr_route_provider');
        $container->setAlias($this->getAlias() . '.content_repository', $this->getAlias() . '.phpcr_content_repository');

        if (isset($config['locales']) && $config['locales']) {
            $container->setParameter($this->getAlias() . '.locales', $config['locales']);
        } else {
            $container->removeDefinition('cmf_routing.phpcrodm_route_locale_listener');
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

        $loader->load('admin_phpcr.xml');
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
