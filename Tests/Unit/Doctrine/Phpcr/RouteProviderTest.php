<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Doctrine\Phpcr;

use Doctrine\Common\Collections\ArrayCollection;
use PHPCR\Query\QueryInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RouteProvider;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

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
        $this->objectManager = $this
            ->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->objectManager2 = $this
            ->getMockBuilder('Doctrine\ODM\PHPCR\DocumentManager')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->managerRegistry
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManager))
        ;
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

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');

        $routeProvider->setPrefixes(array('/cms/routes/'));
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

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefixes(array('/cms/routes/'));
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

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefixes(array('/cms/routes/'));
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

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');

        $routeProvider->setPrefixes(array('/cms/routes'));

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

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');

        $routeProvider->setPrefixes(array(''));
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

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefixes(array(''));
        $routeProvider->getRouteByName('/cms/routes/test-route');
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGetRouteByNameNoRoutePrefixEmptyString()
    {
        $this->objectManager
            ->expects($this->once())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this))
        ;

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefixes(array(''));

        $routeProvider->getRouteByName('/cms/routes/test-route');
    }

    public function testGetRoutesByNames()
    {
        $paths = array(
            '/cms/routes/test-route',
            '/cms/simple/other-route',
            '/cms/routes/not-a-route',
        );

        $collection = new ArrayCollection();
        $collection->set('/cms/routes/test-route', new Route('/test-route'));
        $collection->set('/cms/simple/other-route', new Route('/other-route'));
        $collection->set('/cms/routes/not-a-route', $this);

        $this->objectManager
            ->expects($this->once())
            ->method('findMany')
            ->with(null, $paths)
            ->will($this->returnValue($collection))
        ;
        $paths[] = '/outside/prefix';

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefixes(array('/cms/routes', '/cms/simple'));
        $routeProvider->setRouteCollectionLimit(1);

        $collection = $routeProvider->getRoutesByNames($paths);
        $this->assertCount(2, $collection);
    }

    private function doRouteDump($limit)
    {
        if ($limit === 0) {
            $this->objectManager
                ->expects($this->never())
                ->method('createPhpcrQuery')
            ;
            $this->objectManager
                ->expects($this->never())
                ->method('getDocumentsByPhpcrQuery')
            ;
        } else {
            $query = $this->getMock('\PHPCR\Query\QueryInterface');
            $sql2 = 'SELECT * FROM [nt:unstructured] WHERE [phpcr:classparents] = "Symfony\Component\Routing\Route" AND (ISDESCENDANTNODE("/cms/routes") OR ISDESCENDANTNODE("/cms/simple"))';
            $this->objectManager
                ->expects($this->once())
                ->method('createPhpcrQuery')
                ->with($sql2, QueryInterface::JCR_SQL2)
                ->will($this->returnValue($query))
            ;
            if ($limit) {
                $query
                    ->expects($this->once())
                    ->method('setLimit')
                    ->with($limit)
                ;
            } else {
                $query
                    ->expects($this->never())
                    ->method('setLimit')
                ;
            }
            $this->objectManager
                ->expects($this->once())
                ->method('getDocumentsByPhpcrQuery')
                ->with($query)
                ->will($this->returnValue(new ArrayCollection()))
            ;
            $this->objectManager
                ->expects($this->any())
                ->method('quote')
                ->will(
                    $this->returnCallback(
                        function ($text) {
                            return '"' . $text . '"';
                        }
                    )
                )
            ;
        }

        $routeProvider = new RouteProvider($this->managerRegistry);
        $routeProvider->setManagerName('default');
        $routeProvider->setPrefixes(array('/cms/routes', '/cms/simple'));
        $routeProvider->setRouteCollectionLimit($limit);

        $routeProvider->getRoutesByNames();
    }

    public function testDumpRoutesNoLimit()
    {
        $this->doRouteDump(null);
    }

    public function testDumpRoutesLimit()
    {
        $this->doRouteDump(1);
    }

    public function testDumpRoutesDisabled()
    {
        $this->doRouteDump(0);
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
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
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
        $routeProvider->setPrefixes(array('/cms/routes/'));

        $foundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $foundRoute);
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());

        $routeProvider->setManagerName('new_manager');
        $newFoundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $newFoundRoute);
        $this->assertEquals('/cms/routes/new-route', $newFoundRoute->getPath());
    }
}
