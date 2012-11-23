<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\DependencyInjection\Compiler;

use Symfony\Cmf\Bundle\RoutingExtraBundle\DependencyInjection\Compiler\SetRouterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SetRouterPassTest extends \PHPUnit_Framework_TestCase
{
    public function testMapperPassReplacesRouterAlias()
    {
        $pass = new SetRouterPass();

        $builder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $builder->expects($this->once())
            ->method('getParameter')
            ->with('symfony_cmf_routing_extra.replace_symfony_router')
            ->will($this->returnValue(true));
        $builder->expects($this->once())
            ->method('setAlias')
            ->with('router', 'symfony_cmf_routing_extra.router');

        $pass->process($builder);
    }

    public function testMapperPassDoesntReplaceRouterAlias()
    {
        $pass = new SetRouterPass();

        $builder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $builder->expects($this->once())
            ->method('getParameter')
            ->with('symfony_cmf_routing_extra.replace_symfony_router')
            ->will($this->returnValue(false));
        $builder->expects($this->never())
            ->method('setAlias');

        $pass->process($builder);
    }
}
