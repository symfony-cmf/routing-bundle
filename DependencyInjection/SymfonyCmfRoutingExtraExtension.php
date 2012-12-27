<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection;

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
class SymfonyCmfRoutingExtraExtension extends Extension
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

        $loader->load('form_type.xml');
        // if there is twig, register our form type with twig
        if ($container->hasParameter('twig.form.resources')) {
            $resources = $container->getParameter('twig.form.resources');
            $container->setParameter('twig.form.resources', array_merge($resources, array('SymfonyCmfRoutingExtraBundle:Form:terms_form_type.html.twig')));
        }

        if ($config['use_sonata_admin']) {
            $this->loadSonataAdmin($config, $loader, $container);
        }
    }

    /**
     * Set up the DynamicRouter - only to be called if enabled is set to true
     *
     * @param array $config the compiled configuration for the dynamic router
     * @param ContainerBuilder $container the container builder
     * @param LoaderInterface $loader the configuration loader
     */
    private function setupDynamicRouter(array $config, ContainerBuilder $container, LoaderInterface $loader)
    {
        if (isset($config['enabled']) && false == $config['enabled']) {
            // prevent wtf if somebody is playing around with configs
            return;
        }
        $container->setParameter($this->getAlias() . '.generic_controller', $config['generic_controller']);
        $container->setParameter($this->getAlias() . '.controllers_by_alias', $config['controllers_by_alias']);
        $container->setParameter($this->getAlias() . '.controllers_by_class', $config['controllers_by_class']);
        $container->setParameter($this->getAlias() . '.templates_by_class', $config['templates_by_class']);
        // if the content class defines the template, we also need to make sure we use the generic controller for those routes
        $controllerForTemplates = array();
        foreach ($config['templates_by_class'] as $key => $value) {
            $controllerForTemplates[$key] = $config['generic_controller'];
        }
        $container->setParameter($this->getAlias() . '.defined_templates_class', $controllerForTemplates);
        $container->setParameter($this->getAlias() . '.uri_filter_regexp', $config['uri_filter_regexp']);

        $loader->load('cmf_routing.xml');
        $container->setParameter($this->getAlias() . '.routing_repositoryroot', $config['routing_repositoryroot']);

        $container->setAlias('symfony_cmf_routing_extra.route_provider', $config['route_provider_service_id']);
        $container->setAlias('symfony_cmf_routing_extra.content_repository', $config['content_repository_service_id']);

        $managerRegistry = $container->getDefinition($this->getAlias() . '.manager_registry');
        $managerRegistry->setFactoryService(new Reference($config['manager_registry']));
        $managerRegistry->replaceArgument(0, $config['manager_name']);

        $dynamic = $container->getDefinition($this->getAlias().'.dynamic_router');

        // if any mappings are defined, set the respective route enhancer
        if (!empty($config['generic_controller'])) {
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_explicit_template')));
        }
        if (!empty($config['controllers_by_alias'])) {
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_controllers_by_alias')));
        }
        if (!empty($config['controllers_by_class'])) {
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_controllers_by_class')));
        }
        if (!empty($config['generic_controller']) && !empty($config['templates_by_class'])) {
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_controller_for_templates_by_class')));
            $dynamic->addMethodCall('addRouteEnhancer', array(new Reference($this->getAlias() . '.enhancer_templates_by_class')));
        }
    }

    public function loadSonataAdmin($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_sonata_admin'] && !isset($bundles['SonataDoctrinePHPCRAdminBundle'])) {
            return;
        }

        $loader->load('routing-extra-admin.xml');
        $container->setParameter($this->getAlias() . '.content_basepath', $config['content_basepath']);
        $container->setParameter($this->getAlias() . '.route_basepath', $config['route_basepath']);
    }
}
