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
        // TODO move this to the Configuration class as soon as it supports setting such a default
        array_unshift($configs,
            array('chain' => array(
                'routers_by_id' => array(
                    'router.default' => 100,
                ),
            ),
        ));

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
        // only replace the default router by overwriting the 'router' alias if config tells us to
        if ($config['chain']['replace_symfony_router']) {
            $container->setAlias('router', $this->getAlias() . '.router');
        }

        // add the routers defined in the configuration mapping
        $router = $container->getDefinition($this->getAlias() . '.router');
        foreach ($config['chain']['routers_by_id'] as $id => $priority) {
            $router->addMethodCall('add', array(new Reference($id), $priority));
        }

        $loader->load('form_type.xml');
        // if there is twig, register our form type with twig
        if ($container->hasParameter('twig.form.resources')) {
            $resources = $container->getParameter('twig.form.resources');
            $container->setParameter('twig.form.resources',array_merge(array('SymfonyCmfRoutingExtraBundle:Form:terms_form_type.html.twig'), $resources));
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

        $loader->load('cmf_routing.xml');
        $container->setParameter($this->getAlias() . '.routing_repositoryroot', $config['routing_repositoryroot']);

        $container->setAlias('symfony_cmf_routing_extra.route_repository', $config['route_repository_service_id']);
        $container->setAlias('symfony_cmf_routing_extra.content_repository', $config['content_repository_service_id']);

        $managerRegistry = $container->getDefinition($this->getAlias() . '.manager_registry');
        $managerRegistry->setFactoryService(new Reference($config['manager_registry']));
        $managerRegistry->replaceArgument(0, $config['manager_name']);

        $dynamic = $container->getDefinition($this->getAlias().'.dynamic_router');

        // if any mappings are defined, set the respective controller mapper
        if (!empty($config['generic_controller'])) {
            $dynamic->addMethodCall('addControllerMapper', array(new Reference($this->getAlias() . '.mapper_explicit_template')));
        }
        if (!empty($config['controllers_by_alias'])) {
            $dynamic->addMethodCall('addControllerMapper', array(new Reference($this->getAlias() . '.mapper_controllers_by_alias')));
        }
        if (!empty($config['controllers_by_class'])) {
            $dynamic->addMethodCall('addControllerMapper', array(new Reference($this->getAlias() . '.mapper_controllers_by_class')));
        }

        if (!empty($config['generic_controller']) && !empty($config['templates_by_class'])) {
            $dynamic->addMethodCall('addControllerMapper', array(new Reference($this->getAlias() . '.mapper_templates_by_class')));
        }
    }

    public function loadSonataAdmin($config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ('auto' === $config['use_sonata_admin'] && !isset($bundles['SonataDoctrinePHPCRAdminBundle'])) {
            return;
        }

        $loader->load('routing-extra-admin.xml');
        $contentBasepath = $config['content_basepath'];
        if (null === $contentBasepath) {
            if ($container->hasParameter('symfony_cmf_content.content_basepath')) {
                $contentBasepath = $container->getParameter('symfony_cmf_content.content_basepath');
            } else {
                $contentBasepath = '/cms/content';
            }
        }
        $container->setParameter($this->getAlias() . '.content_basepath', $contentBasepath);
        $container->setParameter($this->getAlias() . '.route_basepath', $config['route_basepath']);
    }
}
