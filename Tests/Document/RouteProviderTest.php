<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Document;

use Symfony\Cmf\Bundle\RoutingBundle\Document\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Document\RouteProvider;

class RouteProviderTest extends \PHPUnit_Framework_Testcase
{
    protected $routeProvider;

    public function setUp()
    {
        $dm = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->routeProvider = new RouteProvider($dm);
    }

    public function testGetRouteCollectionForNode()
    {
        $routeCollection = $this->routeProvider->getRouteCollectionForHierarchy($this->generateRouteHierarchy());
        $this->assertInstanceOf('\Symfony\Component\Routing\RouteCollection', $routeCollection);
        $this->assertEquals(3, $routeCollection->count());
    }

    /**
     * @return \Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route
     */
    protected function generateRouteHierarchy()
    {
        $route1 = new Route();
        $route1->setId('/route/main');
        $route11 = new Route();
        $route11->setId('/route/main/1');
        $route12 = new Route();
        $route12->setId('/route/main/2');

        $refl = new \ReflectionClass($route1);
        $prop = $refl->getProperty('children');
        $prop->setAccessible(true);
        $prop->setValue($route1, array(
            $route11,
            $route12,
        ));

        return $route1;
    }
}
