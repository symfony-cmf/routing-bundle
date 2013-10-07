<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RouteProvider;

class RouteProviderTest extends \PHPUnit_Framework_Testcase
{
    private $route;
    private $route2;
    private $objectManager;
    private $objectManager2;
    private $managerRegistry;

    public function setUp()
    {
        $this->route = $this->getMockBuilder('Symfony\Component\Routing\Route')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->route2 = $this->getMockBuilder('Symfony\Component\Routing\Route')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->objectManager2 = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
    }

    public function testGetRouteCollectionForRequest()
    {
        $this->markTestIncomplete();
    }

    public function testGetRouteByName()
    {

        $this->route
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/routes/test-route'))
        ;

        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this->route))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');

        $routeProvider->setPrefix('/cms/routes/');
        $foundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');

        $this->assertInstanceOf('Symfony\Component\Routing\Route', $foundRoute);
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGetRouteByNameNotFound()
    {
        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue(null))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefix('/cms/routes/');
        $routeProvider->getRouteByName('/cms/routes/test-route');
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGetRouteByNameNoRoute()
    {
        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefix('/cms/routes/');
        $routeProvider->getRouteByName('/cms/routes/test-route');
    }

    /**
     * @expectedException  \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGetRouteByNameInvalidRoute()
    {
        $this->objectManager
            ->expects($this->never())
            ->method('find')
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');

        $routeProvider->setPrefix('/cms/routes');

        $routeProvider->getRouteByName('invalid_route');
    }

    public function testGetRouteByNameIdPrefixEmptyString()
    {

        $this->route
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/routes/test-route'))
        ;

        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this->route))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');

        $routeProvider->setPrefix('');
        $foundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');

        $this->assertInstanceOf('Symfony\Component\Routing\Route', $foundRoute);
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());
    }


    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGetRouteByNameNotFoundIdPrefixEmptyString()
    {
        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue(null))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefix('');
        $routeProvider->getRouteByName('/cms/routes/test-route');
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGetRouteByNameNoRoutePrefixEmptyString()
    {
        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefix('');

        $routeProvider->getRouteByName('/cms/routes/test-route');
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
        $this->route
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/routes/test-route'));

        $this->route2
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/routes/new-route'));

        $this->objectManager
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this->route))
        ;

        $this->objectManager2
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this->route2))
        ;

        $objectManagers = array(
            'default' => $this->objectManager,
            'new_manager' => $this->objectManager2
        );
        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will(
                $this->returnCallback(
                    function ($name) use ($objectManagers) {
                        return $objectManagers[$name];
                    }
                )
            );

        $routeProvider = new RouteProvider($this->managerRegistry);

        $routeProvider->setManagerName('default');
        $routeProvider->setPrefix('/cms/routes/');

        $foundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $foundRoute);
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());

        $routeProvider->setManagerName('new_manager');
        $newFoundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $newFoundRoute);
        $this->assertEquals('/cms/routes/new-route', $newFoundRoute->getPath());
    }
}
