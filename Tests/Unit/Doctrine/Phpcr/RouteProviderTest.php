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
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ODM\PHPCR\DocumentManager;
use PHPCR\Query\QueryInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RouteProvider;
use Symfony\Cmf\Component\Routing\Candidates\PrefixCandidates;
use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteProviderTest extends CmfUnitTestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $managerRegistryMock;

    /**
     * @var PrefixCandidates|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $candidatesMock;

    /**
     * @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dmMock;
    /**
     * @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dm2Mock;

    /**
     * @var Route|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $routeMock;
    /**
     * @var Route|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $route2Mock;

    public function setUp()
    {
        $this->routeMock = $this->buildMock('Symfony\Component\Routing\Route');
        $this->route2Mock = $this->buildMock('Symfony\Component\Routing\Route');
        $this->dmMock = $this->buildMock('Doctrine\ODM\PHPCR\DocumentManager');
        $this->dm2Mock = $this->buildMock('Doctrine\ODM\PHPCR\DocumentManager');
        $this->managerRegistryMock = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->managerRegistryMock
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->dmMock))
        ;

        $this->candidatesMock = $this->getMock('Symfony\Cmf\Component\Routing\Candidates\CandidatesInterface');
    }

    public function testGetRouteCollectionForRequest()
    {
        $request = Request::create('/my/path');
        $candidates = array('/prefix/my/path', '/prefix/my');

        $this->candidatesMock
            ->expects($this->once())
            ->method('getCandidates')
            ->with($request)
            ->will($this->returnValue($candidates))
        ;

        $objects = array(
            new Route('/my'),
            $this,
        );

        $this->dmMock
            ->expects($this->once())
            ->method('findMany')
            ->with(null, $candidates)
            ->will($this->returnValue($objects))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $collection = $routeProvider->getRouteCollectionForRequest($request);
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $collection);
        $this->assertCount(1, $collection);
    }

    public function testGetRouteCollectionForRequestEmpty()
    {
        $request = Request::create('/my/path');

        $this->candidatesMock
            ->expects($this->once())
            ->method('getCandidates')
            ->with($request)
            ->will($this->returnValue(array()))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $collection = $routeProvider->getRouteCollectionForRequest($request);
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $collection);
        $this->assertCount(0, $collection);
    }

    public function testGetRouteByName()
    {
        $this->routeMock
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/routes/test-route'))
        ;

        $this->dmMock
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this->routeMock))
        ;

        $this->candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->will($this->returnValue(true))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $foundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');

        $this->assertInstanceOf('Symfony\Component\Routing\Route', $foundRoute);
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGetRouteByNameNotFound()
    {
        $this->dmMock
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue(null))
        ;

        $this->candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->will($this->returnValue(true))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');
        $routeProvider->getRouteByName('/cms/routes/test-route');
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGetRouteByNameNoRoute()
    {
        $this->dmMock
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this))
        ;
        $this->candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->will($this->returnValue(true))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');
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

        $this->dmMock
            ->expects($this->once())
            ->method('findMany')
            ->with(null, $paths)
            ->will($this->returnValue($collection))
        ;

        $this->candidatesMock
            ->expects($this->at(0))
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->will($this->returnValue(true))
        ;
        $this->candidatesMock
            ->expects($this->at(1))
            ->method('isCandidate')
            ->with('/cms/simple/other-route')
            ->will($this->returnValue(true))
        ;
        $this->candidatesMock
            ->expects($this->at(2))
            ->method('isCandidate')
            ->with('/cms/routes/not-a-route')
            ->will($this->returnValue(true))
        ;
        $this->candidatesMock
            ->expects($this->at(3))
            ->method('isCandidate')
            ->with('/outside/prefix')
            ->will($this->returnValue(false))
        ;

        $paths[] = '/outside/prefix';

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $collection = $routeProvider->getRoutesByNames($paths);
        $this->assertCount(2, $collection);
    }

    private function doRouteDump($limit)
    {
        $from = $this->getMock('Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder', array('document'));
        $from->expects($this->once())
            ->method('document')
            ->with('Symfony\Component\Routing\Route', 'd')
        ;

        $query = $this->buildMock('Doctrine\ODM\PHPCR\Query\Query');
        $query->expects($this->once())->method('getResult');
        if ($limit) {
            $query
                ->expects($this->once())
                ->method('setMaxResults')
                ->with($limit)
            ;
        } else {
            $query
                ->expects($this->never())
                ->method('setMaxResults')
            ;
        }

        $queryBuilder = $this->getMock('Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder', array('from', 'getQuery'));
        $queryBuilder->expects($this->once())
            ->method('from')
            ->with('d')
            ->will($this->returnValue($from))
        ;
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query))
        ;

        $this->dmMock
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder))
        ;

        $this->candidatesMock
            ->expects($this->once())
            ->method('restrictQuery')
            ->with($queryBuilder)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');
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
        $this->dmMock
            ->expects($this->never())
            ->method('createPhpcrQuery')
        ;
        $this->dmMock
            ->expects($this->never())
            ->method('getDocumentsByPhpcrQuery')
        ;
        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');
        $routeProvider->setRouteCollectionLimit(0);

        $this->assertEquals(array(), $routeProvider->getRoutesByNames());
    }

    /**
     * Use getRouteByName() with two different document managers.
     * The two document managers will return different route objects when searching for the same path.
     */
    public function testChangingDocumentManager()
    {
        $this->routeMock
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/routes/test-route'));

        $this->route2Mock
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/cms/routes/new-route'));

        $this->dmMock
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this->routeMock))
        ;

        $this->dm2Mock
            ->expects($this->any())
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->will($this->returnValue($this->route2Mock))
        ;

        $objectManagers = array(
            'default' => $this->dmMock,
            'new_manager' => $this->dm2Mock
        );
        $this->managerRegistryMock = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->managerRegistryMock
            ->expects($this->any())
            ->method('getManager')
            ->will(
                $this->returnCallback(
                    function ($name) use ($objectManagers) {
                        return $objectManagers[$name];
                    }
                )
            )
        ;

        $this->candidatesMock
            ->expects($this->any())
            ->method('isCandidate')
            ->will($this->returnValue(true))
        ;
        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);

        $routeProvider->setManagerName('default');

        $foundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $foundRoute);
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());

        $routeProvider->setManagerName('new_manager');
        $newFoundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $newFoundRoute);
        $this->assertEquals('/cms/routes/new-route', $newFoundRoute->getPath());
    }
}
