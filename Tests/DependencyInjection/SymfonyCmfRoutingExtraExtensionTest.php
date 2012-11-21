<?php
namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\DependencyInjection;

use Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection\SymfonyCmfRoutingExtraExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;

class SymfonyCmfRoutingExtraExtensionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param array $config
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function getBuilder(array $config = array())
    {
        $builder = new ContainerBuilder();

        $loader = new SymfonyCmfRoutingExtraExtension();
        $loader->load($config, $builder);

        return $builder;
    }

    public function testLoadDefault()
    {

        $builder = $this->getBuilder(
            array(
                array(
                    'dynamic' => array(),
                    'use_sonata_admin' => false,
                )
            )
        );

        $this->assertTrue($builder->hasAlias('symfony_cmf_routing_extra.route_repository'));
        $alias = $builder->getAlias('symfony_cmf_routing_extra.route_repository');
        $this->assertEquals('symfony_cmf_routing_extra.default_route_repository', $alias->__toString());

        $this->assertTrue($builder->hasAlias('symfony_cmf_routing_extra.content_repository'));
        $alias = $builder->getAlias('symfony_cmf_routing_extra.content_repository');
        $this->assertEquals('symfony_cmf_routing_extra.default_content_repository', $alias->__toString());

        $this->assertTrue($builder->hasAlias('router'));
        $alias = $builder->getAlias('router');
        $this->assertEquals('symfony_cmf_routing_extra.router', $alias->__toString());

        $this->assertTrue($builder->hasDefinition('symfony_cmf_routing_extra.router'));
        $methodCalls = $builder->getDefinition('symfony_cmf_routing_extra.router')->getMethodCalls();
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
                    'route_repository_service_id' => 'test_route_repository_service',
                    'content_repository_service_id' => 'test_content_repository_service',
                ),
                'use_sonata_admin' => false,
                'chain' => array(
                    'routers_by_id' => $providedRouters = array(
                        'router.custom' => 200,
                        'router.default' => 300
                    )
                )
            )
        );

        $builder = $this->getBuilder($config);

        $this->assertTrue($builder->hasAlias('symfony_cmf_routing_extra.route_repository'));
        $alias = $builder->getAlias('symfony_cmf_routing_extra.route_repository');
        $this->assertEquals('test_route_repository_service', $alias->__toString());

        $this->assertTrue($builder->hasAlias('symfony_cmf_routing_extra.content_repository'));
        $alias = $builder->getAlias('symfony_cmf_routing_extra.content_repository');
        $this->assertEquals('test_content_repository_service', $alias->__toString());

        $this->assertTrue($builder->hasDefinition('symfony_cmf_routing_extra.router'));
        $methodCalls = $builder->getDefinition('symfony_cmf_routing_extra.router')->getMethodCalls();
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

}
