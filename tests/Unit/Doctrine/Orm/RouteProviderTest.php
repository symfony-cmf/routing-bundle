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

class RouteProviderTest extends TestCase
{
    /**
     * @var Route&MockObject
     */
    private Route $routeMock;

    /**
     * @var Route&MockObject
     */
    private Route $route2Mock;

    /**
     * @var ManagerRegistry&MockObject
     */
    private ManagerRegistry $managerRegistryMock;

    /**
     * @var ObjectManager&MockObject
     */
    private ObjectManager $objectManagerMock;

    /**
     * @var EntityRepository&MockObject
     */
    private EntityRepository $objectRepositoryMock;

    /**
     * @var CandidatesInterface&MockObject
     */
    private CandidatesInterface $candidatesMock;

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
            ->method('isCandidate')
            ->willReturn(true)
        ;

        $this->managerRegistryMock
            ->method('getManager')
            ->willReturn($this->objectManagerMock)
        ;
        $this->objectManagerMock
            ->method('getRepository')
            ->with('Route')
            ->willReturn($this->objectRepositoryMock)
        ;
    }

    public function testGetRouteCollectionForRequest(): void
    {
        $request = Request::create('/my/path');
        $candidates = ['/my/path', '/my', '/'];

        $this->candidatesMock
            ->expects($this->once())
            ->method('getCandidates')
            ->with($request)
            ->willReturn($candidates)
        ;

        $this->routeMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('/my/path')
        ;
        $this->route2Mock
            ->expects($this->once())
            ->method('getName')
            ->willReturn('/my')
        ;
        $objects = [
            $this->routeMock,
            $this->route2Mock,
        ];

        $this->objectRepositoryMock
            ->expects($this->once())
            ->method('findByStaticPrefix')
            ->with($candidates, ['position' => 'ASC'])
            ->willReturn($objects)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $collection = $routeProvider->getRouteCollectionForRequest($request);
        $this->assertCount(2, $collection);
    }

    public function testGetRouteCollectionForRequestEmpty(): void
    {
        $request = Request::create('/my/path');

        $this->candidatesMock
            ->expects($this->once())
            ->method('getCandidates')
            ->with($request)
            ->willReturn([])
        ;

        $this->objectRepositoryMock
            ->expects($this->never())
            ->method('findByStaticPrefix')
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $collection = $routeProvider->getRouteCollectionForRequest($request);
        $this->assertCount(0, $collection);
    }

    public function testGetRouteByName(): void
    {
        $this->objectRepositoryMock
            ->method('findOneBy')
            ->with(['name' => '/test-route'])
            ->willReturn($this->routeMock)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $routeProvider->setManagerName('default');

        $foundRoute = $routeProvider->getRouteByName('/test-route');

        $this->assertSame($this->routeMock, $foundRoute);
    }

    public function testGetRouteByNameNotFound(): void
    {
        $this->objectRepositoryMock
            ->method('findOneBy')
            ->with(['name' => '/test-route'])
            ->willReturn(null)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $routeProvider->setManagerName('default');

        $this->expectException(RouteNotFoundException::class);
        $routeProvider->getRouteByName('/test-route');
    }

    public function testGetRouteByNameNotCandidate(): void
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
            ->willReturn(false)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $candidatesMock, 'Route');
        $routeProvider->setManagerName('default');

        $this->expectException(RouteNotFoundException::class);
        $routeProvider->getRouteByName('/test-route');
    }

    public function testGetRoutesByNames(): void
    {
        $paths = [
            '/test-route',
            '/other-route',
        ];

        $this->objectRepositoryMock
            ->expects($this->at(0))
            ->method('findOneBy')
            ->with(['name' => $paths[0]])
            ->willReturn($this->routeMock)
        ;
        $this->objectRepositoryMock
            ->expects($this->at(1))
            ->method('findOneBy')
            ->with(['name' => $paths[1]])
            ->willReturn($this->routeMock)
        ;

        $paths[] = '/no-candidate';

        $candidatesMock = $this->createMock(CandidatesInterface::class);
        $candidatesMock
            ->expects($this->at(0))
            ->method('isCandidate')
            ->with($paths[0])
            ->willReturn(true)
        ;
        $candidatesMock
            ->expects($this->at(1))
            ->method('isCandidate')
            ->with($paths[1])
            ->willReturn(true)
        ;
        $candidatesMock
            ->expects($this->at(2))
            ->method('isCandidate')
            ->with($paths[2])
            ->willReturn(false)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $candidatesMock, 'Route');
        $routeProvider->setManagerName('default');

        $routes = $routeProvider->getRoutesByNames($paths);
        $this->assertCount(2, $routes);
    }

    public function testGetAllRoutesDisabled(): void
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

    public function testGetAllRoutes(): void
    {
        $this->objectRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with([], null, 42)
            ->willReturn([$this->routeMock])
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock, 'Route');
        $routeProvider->setManagerName('default');
        $routeProvider->setRouteCollectionLimit(42);

        $routes = $routeProvider->getRoutesByNames(null);
        $this->assertCount(1, $routes);
    }
}
