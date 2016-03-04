<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\CmfRoutingExtension;
use Symfony\Component\DependencyInjection\Reference;

class CmfRoutingExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new CmfRoutingExtension(),
        );
    }

    public function testLoadDefault()
    {
        $this->load(array(
            'dynamic' => array(
                'enabled' => true,
                'persistence' => array(
                    'phpcr' => array(
                        'enabled' => true,
                        'use_sonata_admin' => false,
                    ),
                ),
            ),
        ));

        $this->assertContainerBuilderHasAlias('cmf_routing.route_provider', 'cmf_routing.phpcr_route_provider');
        $this->assertContainerBuilderHasAlias('cmf_routing.content_repository', 'cmf_routing.phpcr_content_repository');

        $this->assertContainerBuilderHasParameter('cmf_routing.replace_symfony_router', true);

        $this->assertContainerBuilderHasService('cmf_routing.router', 'Symfony\Cmf\Component\Routing\ChainRouter');
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_routing.router', 'add', array(
            new Reference('router.default'),
            100,
        ));
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.route_content',
            'dynamic_router_route_enhancer',
            array('priority' => 100)
        );
    }

    public function testLoadConfigured()
    {
        $this->load(array(
            'dynamic' => array(
                'enabled' => true,
                'route_provider_service_id' => 'test_route_provider_service',
                'content_repository_service_id' => 'test_content_repository_service',
                'persistence' => array(
                    'phpcr' => array(
                        'use_sonata_admin' => false,
                    ),
                ),
            ),
            'chain' => array(
                'routers_by_id' => array(
                    'router.custom' => 200,
                    'router.default' => 300,
                ),
            ),
        ));

        $this->assertContainerBuilderHasAlias('cmf_routing.route_provider', 'test_route_provider_service');
        $this->assertContainerBuilderHasAlias('cmf_routing.content_repository', 'test_content_repository_service');

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_routing.router', 'add', array(
            new Reference('router.custom'),
            200,
        ));
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_routing.router', 'add', array(
            new Reference('router.default'),
            300,
        ));
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.content_repository',
            'dynamic_router_route_enhancer',
            array('priority' => 100)
        );
    }

    public function testWhitespaceInPriorities()
    {
        $this->load(array(
            'dynamic' => array(
                'route_provider_service_id' => 'test_route_provider_service',
                'enabled' => true,
                'controllers_by_type' => array(
                    'Acme\Foo' => '
                        acme_main.controller:indexAction
                    ',
                ),
            ),
            'chain' => array(
                'routers_by_id' => array(
                    'acme_test.router' => '
                        100
                    ',
                ),
            ),
        ));

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_routing.router', 'add', array(
            new Reference('acme_test.router'),
            100,
        ));

        $this->assertContainerBuilderHasParameter('cmf_routing.controllers_by_type', array(
            'Acme\Foo' => 'acme_main.controller:indexAction',
        ));

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.controllers_by_type',
            'dynamic_router_route_enhancer',
            array('priority' => 60)
        );
    }

    /**
     * @dataProvider getBasePathsTests
     */
    public function testLoadBasePaths($phpcrConfig, $routeBasepathsParameter, $adminBasePathParameter)
    {
        $this->container->setParameter(
            'kernel.bundles',
            array(
                'CmfRoutingBundle' => true,
                'SonataDoctrinePHPCRAdminBundle' => true,
            )
        );

        if (!isset($phpcrConfig['enabled'])) {
            $phpcrConfig['enabled'] = true;
        }

        $this->load(array(
            'dynamic' => array(
                'enabled' => true,
                'persistence' => array(
                    'phpcr' => $phpcrConfig,
                ),
            ),
        ));

        $this->assertContainerBuilderHasParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths', $routeBasepathsParameter);
        $this->assertContainerBuilderHasParameter('cmf_routing.dynamic.persistence.phpcr.admin_basepath', $adminBasePathParameter);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.content_repository',
            'dynamic_router_route_enhancer',
            array('priority' => 100)
        );
    }

    public function getBasePathsTests()
    {
        return array(
            array(
                array(),
                array('/cms/routes'),
                '/cms/routes',
            ),

            array(
                array('route_basepaths' => '/cms/test'),
                array('/cms/test'),
                '/cms/test',
            ),

            array(
                array('route_basepaths' => array('/cms/routes', '/cms/test')),
                array('/cms/routes', '/cms/test'),
                '/cms/routes',
            ),

            array(
                array('route_basepaths' => array('/cms/test', '/cms/routes')),
                array('/cms/test', '/cms/routes'),
                '/cms/test',
            ),
        );
    }

    /**
     * @dataProvider getBasePathsMergingTests
     */
    public function testRouteBasepathsMerging($phpcrConfig1, $phpcrConfig2, $routeBasepathsParameter, $adminBasePathParameter)
    {
        $this->container->setParameter(
            'kernel.bundles',
            array(
                'CmfRoutingBundle' => true,
                'SonataDoctrinePHPCRAdminBundle' => true,
            )
        );

        if (!isset($phpcrConfig1['enabled'])) {
            $phpcrConfig1['enabled'] = true;
        }
        if (!isset($phpcrConfig2['enabled'])) {
            $phpcrConfig2['enabled'] = true;
        }

        $configs = array(
            array(
                'dynamic' => array(
                    'enabled' => true,
                    'persistence' => array('phpcr' => $phpcrConfig1),
                ),
            ),
            array(
                'dynamic' => array(
                    'enabled' => true,
                    'persistence' => array('phpcr' => $phpcrConfig2),
                ),
            ),
        );

        foreach ($this->container->getExtensions() as $extension) {
            $extension->load($configs, $this->container);
        }

        $this->assertContainerBuilderHasParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths', $routeBasepathsParameter);
        $this->assertContainerBuilderHasParameter('cmf_routing.dynamic.persistence.phpcr.admin_basepath', $adminBasePathParameter);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.content_repository',
            'dynamic_router_route_enhancer',
            array('priority' => 100)
        );
    }

    public function getBasePathsMergingTests()
    {
        return array(
            array(
                array('route_basepaths' => array('/cms/test')),
                array('route_basepaths' => array('/cms/test2')),
                array('/cms/test', '/cms/test2'),
                '/cms/test',
            ),

            array(
                array('route_basepaths' => array('/cms/test')),
                array('route_basepaths' => array('/cms/test2', '/cms/test3')),
                array('/cms/test', '/cms/test2', '/cms/test3'),
                '/cms/test',
            ),

            array(
                array(),
                array('route_basepaths' => array('/cms/test')),
                array('/cms/test'),
                '/cms/test',
            ),
        );
    }

    public function testLegacyRouteBasepath()
    {
        $this->container->setParameter(
            'kernel.bundles',
            array(
                'CmfRoutingBundle' => true,
                'SonataDoctrinePHPCRAdminBundle' => true,
            )
        );

        $this->load(array(
            'dynamic' => array(
                'enabled' => true,
                'persistence' => array(
                    'phpcr' => array(
                        'route_basepath' => '/cms/test',
                    ),
                ),
            ),
        ));

        $this->assertContainerBuilderHasParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths', array('/cms/test'));
        $this->assertContainerBuilderHasParameter('cmf_routing.dynamic.persistence.phpcr.admin_basepath', '/cms/test');
    }

    public function testInitializerEnabled()
    {
        $this->container->setParameter(
            'kernel.bundles',
            array(
                'CmfRoutingBundle' => true,
                'SonataDoctrinePHPCRAdminBundle' => true,
            )
        );

        $this->load(array(
            'dynamic' => array(
                'enabled' => true,
                'persistence' => array(
                    'phpcr' => array(
                        'enabled' => true,
                        'use_sonata_admin' => true,
                        'enable_initializer' => true,
                    ),
                ),
            ),
        ));

        $this->assertContainerBuilderHasService('cmf_routing.initializer', 'Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer');
    }

    public function testInitializerDisabled()
    {
        $this->container->setParameter(
            'kernel.bundles',
            array(
                'CmfRoutingBundle' => true,
            )
        );

        $this->load(array(
            'dynamic' => array(
                'enabled' => true,
                'persistence' => array(
                    'phpcr' => array(
                        'enabled' => true,
                        'enable_initializer' => false,
                    ),
                ),
            ),
        ));

        $this->assertFalse($this->container->has('cmf_routing.initializer'));
    }

    public function testInitializerEnabledEvenWithoutSonata()
    {
        $this->load(array(
                'dynamic' => array(
                    'enabled' => true,
                    'persistence' => array(
                        'phpcr' => array(
                            'enabled' => true,
                            'use_sonata_admin' => false,
                            'enable_initializer' => true,
                        ),
                    ),
                ),
            ));

        $this->assertTrue($this->container->has('cmf_routing.initializer'));
    }

    public function testInitializerEnabledAutomaticallyIfSonataIsEnabled()
    {
        $this->container->setParameter(
            'kernel.bundles',
            array(
                'CmfRoutingBundle' => true,
                'SonataDoctrinePHPCRAdminBundle' => true,
            )
        );

        $this->load(array(
            'dynamic' => array(
                'enabled' => true,
                'persistence' => array(
                    'phpcr' => array(
                        'enabled' => true,
                        'use_sonata_admin' => true,
                        'enable_initializer' => 'auto',
                    ),
                ),
            ),
        ));

        $this->assertTrue($this->container->has('cmf_routing.initializer'));
    }

    public function testInitializerDisabledAutomaticallyIfSonataIsDisabled()
    {
        $this->load(array(
            'dynamic' => array(
                'enabled' => true,
                'persistence' => array(
                    'phpcr' => array(
                        'enabled' => true,
                        'use_sonata_admin' => false,
                        'enable_initializer' => 'auto',
                    ),
                ),
            ),
        ));

        $this->assertFalse($this->container->has('cmf_routing.initializer'));
    }
}
