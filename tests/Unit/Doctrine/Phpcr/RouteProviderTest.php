<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Doctrine\Phpcr;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Query\Builder\From;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\ODM\PHPCR\Query\Query;
use Doctrine\ODM\PHPCR\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use PHPCR\Util\UUIDHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RouteProvider;
use Symfony\Cmf\Component\Routing\Candidates\CandidatesInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;

class RouteProviderTest extends TestCase
{
    private ManagerRegistry&MockObject $managerRegistryMock;
    private CandidatesInterface&MockObject $candidatesMock;
    private DocumentManager&MockObject $dmMock;
    private DocumentManager&MockObject $dm2Mock;
    private Route&MockObject $routeMock;
    private Route&MockObject $route2Mock;

    protected function setUp(): void
    {
        $this->routeMock = $this->createMock(Route::class);
        $this->route2Mock = $this->createMock(Route::class);
        $this->dmMock = $this->createMock(DocumentManager::class);
        $this->dm2Mock = $this->createMock(DocumentManager::class);
        $this->managerRegistryMock = $this->createMock(ManagerRegistry::class);

        $this->managerRegistryMock
            ->method('getManager')
            ->willReturn($this->dmMock)
        ;

        $this->candidatesMock = $this->createMock(CandidatesInterface::class);
    }

    public function testGetRouteCollectionForRequest(): void
    {
        $request = Request::create('/my/path');
        $candidates = ['/prefix/my/path', '/prefix/my'];

        $this->candidatesMock
            ->expects($this->once())
            ->method('getCandidates')
            ->with($request)
            ->willReturn($candidates)
        ;

        $this->dmMock
            ->expects($this->once())
            ->method('findMany')
            ->with(null, $candidates)
            ->willReturn([new Route('/my')])
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $collection = $routeProvider->getRouteCollectionForRequest($request);
        $this->assertCount(1, $collection);
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

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $collection = $routeProvider->getRouteCollectionForRequest($request);
        $this->assertCount(0, $collection);
    }

    public function testGetRouteByName(): void
    {
        $this->routeMock
            ->method('getPath')
            ->willReturn('/cms/routes/test-route')
        ;

        $this->dmMock
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->willReturn($this->routeMock)
        ;

        $this->candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->willReturn(true)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $foundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');

        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());
    }

    public function testGetRouteByNameUuid(): void
    {
        $uuid = UUIDHelper::generateUUID();
        $this->routeMock
            ->method('getPath')
            ->willReturn('/cms/routes/test-route')
        ;

        $uow = $this->createMock(UnitOfWork::class);
        $this->dmMock
            ->method('find')
            ->with(null, $uuid)
            ->willReturn($this->routeMock)
        ;
        $this->dmMock
            ->method('getUnitOfWork')
            ->willReturn($uow)
        ;
        $uow
            ->method('getDocumentId')
            ->with($this->routeMock)
            ->willReturn('/cms/routes/test-route')
        ;

        $this->candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->willReturn(true)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $foundRoute = $routeProvider->getRouteByName($uuid);

        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());
    }

    public function testGetRouteByNameUuidNotFound(): void
    {
        $uuid = UUIDHelper::generateUUID();

        $this->dmMock
            ->method('find')
            ->with(null, $uuid)
            ->willReturn(null)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $this->expectException(RouteNotFoundException::class);
        $routeProvider->getRouteByName($uuid);
    }

    public function testGetRouteByNameUuidNotCandidate(): void
    {
        $uuid = UUIDHelper::generateUUID();
        $this->routeMock
            ->method('getPath')
            ->willReturn('/cms/routes/test-route')
        ;

        $uow = $this->createMock(UnitOfWork::class);
        $this->dmMock
            ->method('find')
            ->with(null, $uuid)
            ->willReturn($this->routeMock)
        ;
        $this->dmMock
            ->method('getUnitOfWork')
            ->willReturn($uow)
        ;
        $uow
            ->method('getDocumentId')
            ->willReturn('/cms/routes/test-route')
        ;

        $this->candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->willReturn(false)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $this->expectException(RouteNotFoundException::class);
        $routeProvider->getRouteByName($uuid);
    }

    public function testGetRouteByNameNotCandidate(): void
    {
        $this->dmMock
            ->expects($this->never())
            ->method('find')
        ;

        $this->candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->willReturn(false)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $this->expectException(RouteNotFoundException::class);
        $routeProvider->getRouteByName('/cms/routes/test-route');
    }

    public function testGetRouteByNameNotFound(): void
    {
        $this->dmMock
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->willReturn(null)
        ;

        $this->candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->willReturn(true)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $this->expectException(RouteNotFoundException::class);
        $routeProvider->getRouteByName('/cms/routes/test-route');
    }

    public function testGetRouteByNameNoRoute(): void
    {
        $this->dmMock
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->willReturn($this)
        ;
        $this->candidatesMock
            ->expects($this->once())
            ->method('isCandidate')
            ->with('/cms/routes/test-route')
            ->willReturn(true)
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $this->expectException(RouteNotFoundException::class);
        $routeProvider->getRouteByName('/cms/routes/test-route');
    }

    public function testGetRoutesByNames(): void
    {
        $paths = [
            '/cms/routes/test-route',
            '/cms/simple/other-route',
            '/cms/routes/not-a-route',
        ];

        $routes = [
            '/cms/routes/test-route' => new Route('/test-route'),
            '/cms/simple/other-route' => new Route('/other-route'),
            '/cms/routes/not-a-route' => $this,
        ];

        $this->dmMock
            ->expects($this->once())
            ->method('findMany')
            ->with(null, $paths)
            ->willReturn($routes)
        ;

        $this->candidatesMock
            ->expects($this->exactly(4))
            ->method('isCandidate')
            ->willReturnCallback(function (string $path): bool {
                static $expectedCalls = [
                    ['/cms/routes/test-route', true],
                    ['/cms/simple/other-route', true],
                    ['/cms/routes/not-a-route', true],
                    ['/outside/prefix', false],
                ];

                [$expectedPath, $returnValue] = array_shift($expectedCalls);
                $this->assertSame($expectedPath, $path);

                return $returnValue;
            })
        ;

        $paths[] = '/outside/prefix';

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $routes = $routeProvider->getRoutesByNames($paths);
        $this->assertCount(2, $routes);
    }

    public function testGetRoutesByNamesNotCandidates(): void
    {
        $paths = [
            '/cms/routes/test-route',
            '/cms/simple/other-route',
            '/cms/routes/not-a-route',
        ];

        $this->dmMock
            ->expects($this->never())
            ->method('findMany')
        ;

        $this->candidatesMock
            ->expects($this->exactly(3))
            ->method('isCandidate')
            ->willReturnCallback(function (string $path): bool {
                static $expectedCalls = [
                    '/cms/routes/test-route',
                    '/cms/simple/other-route',
                    '/cms/routes/not-a-route',
                ];

                $this->assertSame(array_shift($expectedCalls), $path);

                return false;
            })
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $routes = $routeProvider->getRoutesByNames($paths);
        $this->assertCount(0, $routes);
    }

    public function testGetRoutesByNamesUuid(): void
    {
        $uuid1 = UUIDHelper::generateUUID();
        $uuid2 = UUIDHelper::generateUUID();
        $paths = [
            $uuid1,
            $uuid2,
        ];

        $route1 = new Route('/test-route');
        $route2 = new Route('/other-route');

        $routes = [
            $uuid1 => $route1,
            $uuid2 => $route2,
        ];

        $this->dmMock
            ->expects($this->once())
            ->method('findMany')
            ->with(null, $paths)
            ->willReturn($routes)
        ;

        $uow = $this->createMock(UnitOfWork::class);

        $this->dmMock
            ->method('getUnitOfWork')
            ->willReturn($uow)
        ;
        $uow
            ->expects($this->exactly(2))
            ->method('getDocumentId')
            ->willReturnCallback(function ($route) use ($route1, $route2): string {
                static $expectedCalls = [];

                if ([] === $expectedCalls) {
                    $expectedCalls = [
                        [$route1, '/cms/routes/test-route'],
                        [$route2, '/cms/routes/other-route'],
                    ];
                }

                [$expectedRoute, $returnValue] = array_shift($expectedCalls);
                $this->assertSame($expectedRoute, $route);

                return $returnValue;
            })
        ;

        $this->candidatesMock
            ->expects($this->exactly(2))
            ->method('isCandidate')
            ->willReturnCallback(function (string $path): bool {
                static $expectedCalls = [
                    ['/cms/routes/test-route', true],
                    ['/cms/routes/other-route', false],
                ];

                [$expectedPath, $returnValue] = array_shift($expectedCalls);
                $this->assertSame($expectedPath, $path);

                return $returnValue;
            })
        ;

        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);
        $routeProvider->setManagerName('default');

        $routes = $routeProvider->getRoutesByNames($paths);
        $this->assertCount(1, $routes);
    }

    private function doRouteDump($limit): void
    {
        $from = $this->createMock(From::class);
        $from->expects($this->once())
            ->method('document')
            ->with(Route::class, 'd')
        ;

        $query = $this->createMock(Query::class);
        $query
            ->expects($this->once())
            ->method('getResult')
            ->willReturn(new ArrayCollection([]))
        ;
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

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('from')
            ->with('d')
            ->willReturn($from)
        ;
        $queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->willReturn($query)
        ;

        $this->dmMock
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder)
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

    public function testDumpRoutesNoLimit(): void
    {
        $this->doRouteDump(null);
    }

    public function testDumpRoutesLimit(): void
    {
        $this->doRouteDump(1);
    }

    public function testDumpRoutesDisabled(): void
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

        $this->assertEquals([], $routeProvider->getRoutesByNames());
    }

    /**
     * Use getRouteByName() with two different document managers.
     * The two document managers will return different route objects when searching for the same path.
     */
    public function testChangingDocumentManager(): void
    {
        $this->routeMock
            ->method('getPath')
            ->willReturn('/cms/routes/test-route');

        $this->route2Mock
            ->method('getPath')
            ->willReturn('/cms/routes/new-route');

        $this->dmMock
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->willReturn($this->routeMock)
        ;

        $this->dm2Mock
            ->method('find')
            ->with(null, '/cms/routes/test-route')
            ->willReturn($this->route2Mock)
        ;

        $objectManagers = [
            'default' => $this->dmMock,
            'new_manager' => $this->dm2Mock,
        ];
        $this->managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $this->managerRegistryMock
            ->method('getManager')
            ->willReturnCallback(
                function ($name) use ($objectManagers) {
                    return $objectManagers[$name];
                }
            )
        ;

        $this->candidatesMock
            ->method('isCandidate')
            ->willReturn(true)
        ;
        $routeProvider = new RouteProvider($this->managerRegistryMock, $this->candidatesMock);

        $routeProvider->setManagerName('default');

        $foundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');
        $this->assertEquals('/cms/routes/test-route', $foundRoute->getPath());

        $routeProvider->setManagerName('new_manager');
        $newFoundRoute = $routeProvider->getRouteByName('/cms/routes/test-route');
        $this->assertEquals('/cms/routes/new-route', $newFoundRoute->getPath());
    }
}
