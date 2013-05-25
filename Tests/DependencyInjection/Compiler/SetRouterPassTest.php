<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\DependencyInjection\Compiler;

use Symfony\Cmf\Bundle\RoutingBundle\DependencyInjection\Compiler\SetRouterPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SetRouterPassTest extends \PHPUnit_Framework_TestCase
{
    public function testMapperPassReplacesRouterAlias()
    {
        $pass = new SetRouterPass();

        $builder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $builder->expects($this->once())
            ->method('getParameter')
            ->with('cmf_routing.replace_symfony_router')
            ->will($this->returnValue(true));
        $builder->expects($this->once())
            ->method('setAlias')
            ->with('router', 'cmf_routing.router');

        $pass->process($builder);
    }

    public function testMapperPassDoesntReplaceRouterAlias()
    {
        $pass = new SetRouterPass();

        $builder = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $builder->expects($this->once())
            ->method('getParameter')
            ->with('cmf_routing.replace_symfony_router')
            ->will($this->returnValue(false));
        $builder->expects($this->never())
            ->method('setAlias');

        $pass->process($builder);
    }
}
