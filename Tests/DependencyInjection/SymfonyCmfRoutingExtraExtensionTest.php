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
        $this->assertEquals(
            'Symfony\Cmf\Bundle\RoutingExtraBundle\Document\RouteRepository',
            $this->builder->getParameter('symfony_cmf_routing_extra.route_repository_class')
        );
        $this->assertEquals(
            'Symfony\Cmf\Bundle\RoutingExtraBundle\Document\ContentRepository',
            $this->builder->getParameter('symfony_cmf_routing_extra.content_repository_class')
        );
    }

    public function testLoadPopulated()
    {
        $config = array(
            array(
                'dynamic' => array(
                    'route_repository_class' => 'TestRouteRepository',
                    'content_repository_class' => 'TestContentRepository',
                ),
                'use_sonata_admin' => false,
            )
        );

        $this->ext->load($config, $this->builder);
        $this->assertEquals(
            'TestRouteRepository',
            $this->builder->getParameter('symfony_cmf_routing_extra.route_repository_class')
        );
        $this->assertEquals(
            'TestContentRepository',
            $this->builder->getParameter('symfony_cmf_routing_extra.content_repository_class')
        );
    }
}
