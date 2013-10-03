<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\RouteProvider;

class RouteProviderTest extends \PHPUnit_Framework_Testcase
{
    private $route;
    private $managerRegistry;
    private $objectManager;
    private $objectRepository;

    public function setUp()
    {
        $this->route = $this->getMockBuilder('Symfony\Component\Routing\Route')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
    }

    public function testGetRouteByName()
    {
        $this->route
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/routes/test-route'));

        $this->objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->objectRepository))
        ;

        $this->objectRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(array('name' => '/cms/routes/test-route'))
            ->will($this->returnValue($this->route))
        ;

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');

        $foundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');

        $this->assertInstanceOf('Symfony\Component\Routing\Route', $foundRoute);
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());
    }

    public function testGetRoutesByNames()
    {
        $this->markTestIncomplete();
    }
}
