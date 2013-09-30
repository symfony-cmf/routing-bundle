<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\DependencyInjection;

use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\CmfRoutingExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CmfRoutingExtensionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param  array                                                   $config
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function getBuilder(array $config = array())
    {
        $builder = new ContainerBuilder();

        $loader = new CmfRoutingExtension();
        $loader->load($config, $builder);

        return $builder;
    }

    public function testLoadDefault()
    {

        $builder = $this->getBuilder(
            array(
                array(
                    'dynamic' => array(
                        'enabled' => true,
                        'persistence' => array(
                            'phpcr' => array(
                                'enabled' => true,
                                'use_sonata_admin' => false,
                            ),
                        ),
                    ),
                )
            )
        );

        $this->assertTrue($builder->hasAlias('cmf_routing.route_provider'));
        $alias = $builder->getAlias('cmf_routing.route_provider');
        $this->assertEquals('cmf_routing.phpcr_route_provider', $alias->__toString());

        $this->assertTrue($builder->hasAlias('cmf_routing.content_repository'));
        $alias = $builder->getAlias('cmf_routing.content_repository');
        $this->assertEquals('cmf_routing.phpcr_content_repository', $alias->__toString());

        $this->assertTrue($builder->getParameter('cmf_routing.replace_symfony_router'));

        $this->assertTrue($builder->hasDefinition('cmf_routing.router'));
        $methodCalls = $builder->getDefinition('cmf_routing.router')->getMethodCalls();
        $addMethodCalls = array_filter(
            $methodCalls,
            function ($call) {
                return 'add' == $call[0];
            }
        );

        $this->assertCount(1, $addMethodCalls);
        $addMethodCall = reset($addMethodCalls);

        $params = $addMethodCall[1];
        $this->assertCount(2, $params);

        /** @var $reference \Symfony\Component\DependencyInjection\Reference */
        list($reference, $priority) = $params;

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $reference);
        $this->assertEquals(100, $priority);

        $this->assertEquals('router.default', $reference->__toString());

    }

    public function testLoadConfigured()
    {
        $config = array(
            array(
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
                    'routers_by_id' => $providedRouters = array(
                        'router.custom' => 200,
                        'router.default' => 300
                    )
                )
            )
        );

        $builder = $this->getBuilder($config);

        $this->assertTrue($builder->hasAlias('cmf_routing.route_provider'));
        $alias = $builder->getAlias('cmf_routing.route_provider');
        $this->assertEquals('test_route_provider_service', $alias->__toString());

        $this->assertTrue($builder->hasAlias('cmf_routing.content_repository'));
        $alias = $builder->getAlias('cmf_routing.content_repository');
        $this->assertEquals('test_content_repository_service', $alias->__toString());

        $this->assertTrue($builder->hasDefinition('cmf_routing.router'));
        $methodCalls = $builder->getDefinition('cmf_routing.router')->getMethodCalls();
        $addMethodCalls = array_filter(
            $methodCalls,
            function ($call) {
                return 'add' == $call[0];
            }
        );

        $this->assertCount(2, $addMethodCalls);

        $routersAdded = array();

        foreach ($addMethodCalls as $addMethodCall) {
            $params = $addMethodCall[1];
            $this->assertCount(2, $params);
            /** @var $reference \Symfony\Component\DependencyInjection\Reference */
            list($reference, $priority) = $params;

            $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $reference);

            $routersAdded[$reference->__toString()] = $priority;
        }

        $this->assertEquals($providedRouters, $routersAdded);
    }

    public function testWhitespaceInPriorities()
    {
        $config = array(
            array(
                'dynamic' => array(
                    'route_provider_service_id' => 'test_route_provider_service',
                    'enabled' => true,
                    'controllers_by_type' => array(
                        'Acme\Foo' => '
                            acme_main.controller:indexAction
                        '
                    ),
                ),
                'chain' => array(
                    'routers_by_id' => array(
                        'acme_test.router' => '
                            100
                        ',
                    ),
                ),
            )
        );

        $builder = $this->getBuilder($config);

        $methodCalls = $builder->getDefinition('cmf_routing.router')->getMethodCalls();
        $addMethodCalls = array_filter(
            $methodCalls,
            function ($call) {
                return 'add' == $call[0];
            }
        );

        $this->assertCount(1, $addMethodCalls);

        $methodCall = current($addMethodCalls);

        $this->assertSame('100', $methodCall[1][1]);

        $controllers = $builder->getParameter('cmf_routing.controllers_by_type');
        $this->assertSame('acme_main.controller:indexAction', $controllers['Acme\Foo']);
    }
}
