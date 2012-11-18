<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\DependencyInjection;

use Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection\SymfonyCmfRoutingExtraExtension;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SymfonyCmfRoutingExtraExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->ext = new SymfonyCmfRoutingExtraExtension;
        $this->builder = new ContainerBuilder;

        $paramBag = $this->getMock('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface');
    }

    public function testLoadDefault()
    {
        $config = array(
            array(
                'dynamic' => array(
                ),
                'use_sonata_admin' => false,
            )
        );

        $this->ext->load($config, $this->builder);

        $this->ext->load($config, $this->builder);
        $this->assertTrue($this->builder->hasAlias('symfony_cmf_routing_extra.route_repository'));
        $alias = $this->builder->getAlias('symfony_cmf_routing_extra.route_repository');
        $this->assertEquals('symfony_cmf_routing_extra.default_route_repository', $alias->__toString());

        $this->assertTrue($this->builder->hasAlias('symfony_cmf_routing_extra.content_repository'));
        $alias = $this->builder->getAlias('symfony_cmf_routing_extra.content_repository');
        $this->assertEquals('symfony_cmf_routing_extra.default_content_repository', $alias->__toString());
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
            )
        );

        $this->ext->load($config, $this->builder);
        $this->assertTrue($this->builder->hasAlias('symfony_cmf_routing_extra.route_repository'));
        $alias = $this->builder->getAlias('symfony_cmf_routing_extra.route_repository');
        $this->assertEquals('test_route_repository_service', $alias->__toString());

        $this->assertTrue($this->builder->hasAlias('symfony_cmf_routing_extra.content_repository'));
        $alias = $this->builder->getAlias('symfony_cmf_routing_extra.content_repository');
        $this->assertEquals('test_content_repository_service', $alias->__toString());
    }
}
