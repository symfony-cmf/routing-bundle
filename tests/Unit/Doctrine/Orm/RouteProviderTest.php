<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Doctrine\Orm;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\RouteProvider;
use Symfony\Cmf\Component\Routing\Candidates\CandidatesInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;

class RouteProviderTest extends TestCase
{
    /**
     * @var Route|MockObject
     */
    private $routeMock;

    /**
     * @var Route|MockObject
     */
    private $route2Mock;

    /**
     * @var ManagerRegistry|MockObject
     */
    private $managerRegistryMock;

    /**
     * @var ObjectManager|MockObject
     */
    private $objectManagerMock;

    /**
     * @var EntityRepository|MockObject
     */
    private $objectRepositoryMock;

    /**
     * @var CandidatesInterface|MockObject
     */
    private $candidatesMock;

    public function setUp(): void
    {
        $this->routeMock = $this->createMock(Route::class);
        $this->route2Mock = $this->createMock(Route::class);
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $this->objectRepositoryMock = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['findByStaticPrefix', 'findOneBy', 'findBy'])
            ->getMock();
        $this->candidatesMock = $this->createMock(CandidatesInterface::class);
        $this->candidatesMock
            ->expects($this->any())
            ->method('isCandidate')
            ->will($this->returnValue(true))
        ;

        $this->managerRegistryMock
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->objectManagerMock))
        ;
        $this->objectManagerMock
            ->expects($this->any())
            ->method('getRepository')
            ->with('Route')
            ->will($this->returnValue($this->objectRepositoryMock))
        ;
    }

    public function testGetRouteCollectionForRequest()
    {
        $request = Request::create('/my/path');
        $candidates = ['/my/path', '/my', '/'];

        $this->candidatesMock
            ->expects($this->once())
            ->method('getCandidates')
            ->with($request)
            ->will($this->returnValue($candidates))
        ;

        $this->routeMock
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('/my/path'))
        ;
        $this->route2Mock
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('/my'))
        ;
        $objects = [
            $this->routeMock,
            $this->route2Mock,
        ];

        $this->objectRepositoryMock
            ->expects($this->once())
            ->method('findByStaticPrefix')
            ->with($candidates, ['position' => 'ASC'])
            ->will($this->returnValue($objects))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $collection = $routeProvider->getRouteCollectionForRequest($request);
        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertCount(2, $collection);
    }

    public function testGetRouteCollectionForRequestEmpty()
    {
        $request = Request::create('/my/path');

        $this->candidatesMock
            ->expects($this->once())
            ->method('getCandidates')
            ->with($request)
            ->will($this->returnValue([]))
        ;

        $this->objectRepositoryMock
            ->expects($this->never())
            ->method('findByStaticPrefix')
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $collection = $routeProvider->getRouteCollectionForRequest($request);
        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertCount(0, $collection);
    }

    public function testGetRouteByName()
    {
        $this->objectRepositoryMock
            ->expects($this->any())
            ->method('findOneBy')
            ->with(['name' => '/test-route'])
            ->will($this->returnValue($this->routeMock))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $routeProvider->setManagerName('default');

        $foundRoute = $routeProvider->getRouteByName('/test-route');

        $this->assertSame($this->routeMock, $foundRoute);
    }

    public function testGetRouteByNameNotFound()
    {
        $this->objectRepositoryMock
            ->expects($this->any())
            ->method('findOneBy')
            ->with(['name' => '/test-route'])
            ->will($this->returnValue(null))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $routeProvider->setManagerName('default');

        $this->expectException(RouteNotFoundException::class);
        $routeProvider->getRouteByName('/test-route');
    }

    public function testGetRouteByNameNotCandidate()
    {
        $this->objectRepositoryMock
            ->expects($this->never())
            ->method('findOneBy')
        ;
        $candidatesMock = $this->createMock(CandidatesInterface::class);
        $candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/test-route')
            ->will($this->returnValue(false))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $candidatesMock, 'Route');
        $routeProvider->setManagerName('default');

        $this->expectException(RouteNotFoundException::class);
        $routeProvider->getRouteByName('/test-route');
    }

    public function testGetRoutesByNames()
    {
        $paths = [
            '/test-route',
            '/other-route',
        ];

        $this->objectRepositoryMock
            ->expects($this->at(0))
            ->method('findOneBy')
            ->with(['name' => $paths[0]])
            ->will($this->returnValue($this->routeMock))
        ;
        $this->objectRepositoryMock
            ->expects($this->at(1))
            ->method('findOneBy')
            ->with(['name' => $paths[1]])
            ->will($this->returnValue($this->routeMock))
        ;

        $paths[] = '/no-candidate';

        $candidatesMock = $this->createMock(CandidatesInterface::class);
        $candidatesMock
            ->expects($this->at(0))
            ->method('isCandidate')
            ->with($paths[0])
            ->will($this->returnValue(true))
        ;
        $candidatesMock
            ->expects($this->at(1))
            ->method('isCandidate')
            ->with($paths[1])
            ->will($this->returnValue(true))
        ;
        $candidatesMock
            ->expects($this->at(2))
            ->method('isCandidate')
            ->with($paths[2])
            ->will($this->returnValue(false))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $candidatesMock, 'Route');
        $routeProvider->setManagerName('default');

        $routes = $routeProvider->getRoutesByNames($paths);
        $this->assertCount(2, $routes);
    }

    public function testGetAllRoutesDisabled()
    {
        $this->objectRepositoryMock
            ->expects($this->never())
            ->method('findBy')
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $routeProvider->setRouteCollectionLimit(0);
        $routeProvider->setManagerName('default');

        $routes = $routeProvider->getRoutesByNames(null);
        $this->assertCount(0, $routes);
    }

    public function testGetAllRoutes()
    {
        $this->objectRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with([], null, 42)
            ->will($this->returnValue([$this->routeMock]))
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $routeProvider->setManagerName('default');
        $routeProvider->setRouteCollectionLimit(42);

        $routes = $routeProvider->getRoutesByNames(null);
        $this->assertCount(1, $routes);
    }
}
