<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\DependencyInjection\Compiler;

use Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection\Compiler\MapperPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class MapperPassTest extends \PHPUnit_Framework_TestCase
{
    public function testMapperPass()
    {
        $serviceIds = array(
            'test_mapper' => array(
                0 => array(
                    'id' => 'foo_mapper'
                )
            ),
        );

        $definition = new Definition('router');
        $builder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $builder->expects($this->at(0))
            ->method('hasDefinition')
            ->will($this->returnValue(true));
        $builder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->will($this->returnValue($serviceIds));
        $builder->expects($this->once())
            ->method('getDefinition')
            ->with('symfony_cmf_routing_extra.dynamic_router')
            ->will($this->returnValue($definition));

        $pass = new MapperPass();
        $pass->process($builder);
        $calls = $definition->getMethodCalls();
        $this->assertEquals(1, count($calls));
        $this->assertEquals('addControllerMapper', $calls[0][0]);
    }
}

