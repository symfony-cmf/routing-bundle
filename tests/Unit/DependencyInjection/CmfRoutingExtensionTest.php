<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\DependencyInjection;

use Doctrine\Bundle\PHPCRBundle\Initializer\GenericInitializer;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\CmfRoutingExtension;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Symfony\Component\DependencyInjection\Reference;

class CmfRoutingExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new CmfRoutingExtension(),
        ];
    }

    public function testLoadDefault()
    {
        $this->load([
            'dynamic' => [
                'enabled' => true,
                'persistence' => [
                    'phpcr' => [
                        'enabled' => true,
                    ],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasAlias('cmf_routing.route_provider', 'cmf_routing.phpcr_route_provider');
        $this->assertContainerBuilderHasAlias('cmf_routing.content_repository', 'cmf_routing.phpcr_content_repository');

        $this->assertContainerBuilderHasParameter('cmf_routing.replace_symfony_router', true);

        $this->assertContainerBuilderHasService('cmf_routing.router', ChainRouter::class);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_routing.router', 'add', [
            new Reference('router.default'),
            100,
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.route_content',
            'dynamic_router_route_enhancer',
            ['priority' => 100]
        );
    }

    public function testLoadConfigured()
    {
        $this->load([
            'dynamic' => [
                'enabled' => true,
                'route_provider_service_id' => 'test_route_provider_service',
                'content_repository_service_id' => 'test_content_repository_service',
                'persistence' => [
                    'phpcr' => true,
                ],
            ],
            'chain' => [
                'routers_by_id' => [
                    'router.custom' => 200,
                    'router.default' => 300,
                ],
            ],
        ]);

        $this->assertContainerBuilderHasAlias('cmf_routing.route_provider', 'test_route_provider_service');
        $this->assertContainerBuilderHasAlias('cmf_routing.content_repository', 'test_content_repository_service');

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_routing.router', 'add', [
            new Reference('router.custom'),
            200,
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_routing.router', 'add', [
            new Reference('router.default'),
            300,
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.content_repository',
            'dynamic_router_route_enhancer',
            ['priority' => 100]
        );
    }

    public function testWhitespaceInPriorities()
    {
        $this->load([
            'dynamic' => [
                'route_provider_service_id' => 'test_route_provider_service',
                'enabled' => true,
                'controllers_by_type' => [
                    'Acme\Foo' => '
                        acme_main.controller:indexAction
                    ',
                ],
            ],
            'chain' => [
                'routers_by_id' => [
                    'acme_test.router' => '
                        100
                    ',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('cmf_routing.router', 'add', [
            new Reference('acme_test.router'),
            100,
        ]);

        $this->assertContainerBuilderHasParameter('cmf_routing.controllers_by_type', [
            'Acme\Foo' => 'acme_main.controller:indexAction',
        ]);

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.controllers_by_type',
            'dynamic_router_route_enhancer',
            ['priority' => 60]
        );
    }

    /**
     * @dataProvider getBasePathsTests
     */
    public function testLoadBasePaths($phpcrConfig, $routeBasepathsParameter)
    {
        $this->container->setParameter(
            'kernel.bundles',
            [
                'CmfRoutingBundle' => true,
            ]
        );

        if (!isset($phpcrConfig['enabled'])) {
            $phpcrConfig['enabled'] = true;
        }

        $this->load([
            'dynamic' => [
                'enabled' => true,
                'persistence' => [
                    'phpcr' => $phpcrConfig,
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths', $routeBasepathsParameter);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.content_repository',
            'dynamic_router_route_enhancer',
            ['priority' => 100]
        );
    }

    public function getBasePathsTests()
    {
        return [
            [
                [],
                ['/cms/routes'],
            ],

            [
                ['route_basepaths' => '/cms/test'],
                ['/cms/test'],
            ],

            [
                ['route_basepaths' => ['/cms/routes', '/cms/test']],
                ['/cms/routes', '/cms/test'],
            ],

            [
                ['route_basepaths' => ['/cms/test', '/cms/routes']],
                ['/cms/test', '/cms/routes'],
            ],
        ];
    }

    /**
     * @dataProvider getBasePathsMergingTests
     */
    public function testRouteBasepathsMerging($phpcrConfig1, $phpcrConfig2, $routeBasepathsParameter)
    {
        $this->container->setParameter(
            'kernel.bundles',
            ['CmfRoutingBundle' => true]
        );

        if (!isset($phpcrConfig1['enabled'])) {
            $phpcrConfig1['enabled'] = true;
        }
        if (!isset($phpcrConfig2['enabled'])) {
            $phpcrConfig2['enabled'] = true;
        }

        $configs = [
            [
                'dynamic' => [
                    'enabled' => true,
                    'persistence' => ['phpcr' => $phpcrConfig1],
                ],
            ],
            [
                'dynamic' => [
                    'enabled' => true,
                    'persistence' => ['phpcr' => $phpcrConfig2],
                ],
            ],
        ];

        foreach ($this->container->getExtensions() as $extension) {
            $extension->load($configs, $this->container);
        }

        $this->assertContainerBuilderHasParameter('cmf_routing.dynamic.persistence.phpcr.route_basepaths', $routeBasepathsParameter);
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'cmf_routing.enhancer.content_repository',
            'dynamic_router_route_enhancer',
            ['priority' => 100]
        );
    }

    public function getBasePathsMergingTests()
    {
        return [
            [
                ['route_basepaths' => ['/cms/test']],
                ['route_basepaths' => ['/cms/test2']],
                ['/cms/test', '/cms/test2'],
                '/cms/test',
            ],

            [
                ['route_basepaths' => ['/cms/test']],
                ['route_basepaths' => ['/cms/test2', '/cms/test3']],
                ['/cms/test', '/cms/test2', '/cms/test3'],
                '/cms/test',
            ],

            [
                [],
                ['route_basepaths' => ['/cms/test']],
                ['/cms/test'],
                '/cms/test',
            ],
        ];
    }

    public function testInitializerEnabled()
    {
        $this->container->setParameter(
            'kernel.bundles',
            [
                'CmfRoutingBundle' => true,
            ]
        );

        $this->load([
            'dynamic' => [
                'enabled' => true,
                'persistence' => [
                    'phpcr' => [
                        'enabled' => true,
                        'enable_initializer' => true,
                    ],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasService('cmf_routing.initializer', GenericInitializer::class);
    }

    public function testInitializerDisabled()
    {
        $this->container->setParameter(
            'kernel.bundles',
            [
                'CmfRoutingBundle' => true,
            ]
        );

        $this->load([
            'dynamic' => [
                'enabled' => true,
                'persistence' => [
                    'phpcr' => [
                        'enabled' => true,
                        'enable_initializer' => false,
                    ],
                ],
            ],
        ]);

        $this->assertFalse($this->container->has('cmf_routing.initializer'));
    }

    public function testSettingCustomRouteClassForOrm()
    {
        $this->load([
            'dynamic' => [
                'enabled' => true,
                'persistence' => [
                    'orm' => [
                        'enabled' => true,
                    ],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('cmf_routing.backend_type_orm_default', true);

        $this->load([
            'dynamic' => [
                'enabled' => true,
                'persistence' => [
                    'orm' => [
                        'enabled' => true,
                        'route_class' => 'Some\Bundle\CustomBundle\Doctrine\ORM\Route',
                    ],
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('cmf_routing.backend_type_orm_custom', true);
    }
}
