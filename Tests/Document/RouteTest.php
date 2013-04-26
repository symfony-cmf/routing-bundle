<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Document;

use Symfony\Cmf\Bundle\RoutingBundle\Document\Route;

class RouteTest extends \PHPUnit_Framework_Testcase
{
    public function setUp()
    {
        $this->route = new Route;

        $this->childRoute1 = new Route;
        $this->childRoute1->setName('child route1');
    }

    public function testGetRouteChildren()
    {
        $refl = new \ReflectionClass($this->route);
        $prop = $refl->getProperty('children');
        $prop->setAccessible(true);
        $prop->setValue($this->route, array(
            new \stdClass,
            $this->childRoute1,
        ));

        $res = $this->route->getRouteChildren();
        $this->assertCount(1, $res);
        $this->assertEquals('child route1', $res[0]->getName());
    }

    public function testGetRouteChildrenNull()
    {
        $res = $this->route->getRouteChildren();
        $this->assertEquals(array(), $res);
    }
}
