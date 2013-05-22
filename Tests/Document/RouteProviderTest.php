<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Document;

use Symfony\Cmf\Bundle\RoutingBundle\Document\RouteProvider;

class RouteProviderTest extends \PHPUnit_Framework_Testcase
{
    /** @var RouteProvider **/
    private $routeProvider;

    public function setUp()
    {
        $this->routeProvider = new RouteProvider();
    }

    public function testGetRouteCollectionForRequest()
    {
        $this->markTestIncomplete();
    }

    public function testGetRouteByName()
    {
        $documentManager = $this->getDmMockReturningRoute();

        $this->routeProvider->setDocumentManager($documentManager);
        $foundRoute = $this->routeProvider->getRouteByName('test-route');

        $this->assertInstanceOf('Symfony\Component\Routing\Route', $foundRoute);
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());
    }

    public function testGetRoutesByNames()
    {
        $this->markTestIncomplete();
    }

    /**
     * Use getRouteByName() with two different document managers.
     * The two document managers will return different route objects when searching for the same path.
     */
    public function testChangingDocumentManager()
    {
        $documentManager = $this->getDmMockReturningRoute();

        $newRoute = $this->getMockBuilder('Symfony\Component\Routing\Route')->disableOriginalConstructor()->getMock();
        $newRoute->expects($this->any())->method('getPath')->will($this->returnValue('/cms/routes/new-route'));

        $newDocumentManger  = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $newDocumentManger
            ->expects($this->any())
            ->method('find')
            ->with(null, 'test-route')
            ->will($this->returnValue($newRoute));

        $this->routeProvider->setDocumentManager($documentManager);
        $foundRoute = $this->routeProvider->getRouteByName('test-route');
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $foundRoute);
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());

        $this->routeProvider->setDocumentManager($newDocumentManger);
        $newFoundRoute = $this->routeProvider->getRouteByName('test-route');
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $newFoundRoute);
        $this->assertEquals('/cms/routes/new-route', $newFoundRoute->getPath());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A document manager must be set before using this provider
     */
    public function testGetRouteCollectionForRequestNoDm()
    {
        $requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->routeProvider->getRouteCollectionForRequest($requestMock);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A document manager must be set before using this provider
     */
    public function testGetRouteByNameNoDm()
    {
        $this->routeProvider->getRouteByName('foo');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A document manager must be set before using this provider
     */
    public function testGetRoutesByNamesNoDm()
    {
        $this->routeProvider->getRoutesByNames('foo');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDmMockReturningRoute()
    {
        $route = $this->getMockBuilder('Symfony\Component\Routing\Route')->disableOriginalConstructor()->getMock();
        $route->expects($this->any())->method('getPath')->will($this->returnValue('/cms/routes/test-route'));

        $documentManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $documentManager
            ->expects($this->any())
            ->method('find')
            ->with(null, 'test-route')
            ->will($this->returnValue($route));

        return $documentManager;
    }
}
