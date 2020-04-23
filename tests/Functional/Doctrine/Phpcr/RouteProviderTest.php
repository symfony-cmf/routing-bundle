<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RouteProvider;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

class RouteProviderTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    /** @var RouteProvider */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->db('PHPCR')->createTestNode();
        $this->createRoute(self::ROUTE_ROOT);
        $this->repository = $this->getContainer()->get('cmf_routing.route_provider');
    }

    private function buildRoutes()
    {
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

        $route = new Route();
        $route->setPosition($root, 'testroute');
        $route->setDefault('_format', 'html');
        $this->getDm()->persist($route);

        // smuggle a non-route thing into the repository
        $noroute = new Generic();
        $noroute->setParentDocument($route);
        $noroute->setNodename('noroute');
        $this->getDm()->persist($noroute);

        $childroute = new Route();
        $childroute->setPosition($noroute, 'child');
        $childroute->setDefault('_format', 'json');
        $this->getDm()->persist($childroute);

        $this->getDm()->flush();
        $this->getDm()->clear();
    }

    public function testGetRouteCollectionForRequest()
    {
        $this->buildRoutes();

        $routes = $this->repository->getRouteCollectionForRequest(Request::create('/testroute/noroute/child'));
        $this->assertCount(3, $routes);
        $this->assertContainsOnlyInstancesOf(RouteObjectInterface::class, $routes);

        $iterator = $routes->getIterator();

        $this->assertTrue($iterator->valid());
        $this->assertEquals(self::ROUTE_ROOT.'/testroute/noroute/child', $iterator->key());
        $this->assertEquals('json', $iterator->current()->getDefault('_format'));

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertEquals(self::ROUTE_ROOT.'/testroute', $iterator->key());
        $this->assertEquals('html', $iterator->current()->getDefault('_format'));

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertEquals(self::ROUTE_ROOT, $iterator->key());
        $this->assertNull($iterator->current()->getDefault('_format'));
    }

    public function testGetRouteCollectionForRequestFormat()
    {
        $this->buildRoutes();

        $routes = $this->repository->getRouteCollectionForRequest(Request::create('/testroute/noroute/child.html'));
        $this->assertCount(3, $routes);
        $this->assertContainsOnlyInstancesOf(RouteObjectInterface::class, $routes);

        $iterator = $routes->getIterator();

        $this->assertTrue($iterator->valid());
        $this->assertEquals(self::ROUTE_ROOT.'/testroute/noroute/child', $iterator->key());
        $this->assertEquals('json', $iterator->current()->getDefault('_format'));

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertEquals(self::ROUTE_ROOT.'/testroute', $iterator->key());
        $this->assertEquals('html', $iterator->current()->getDefault('_format'));

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertEquals(self::ROUTE_ROOT, $iterator->key());
        $this->assertNull($iterator->current()->getDefault('_format'));
    }

    /**
     * The root route will always be found.
     */
    public function testGetRouteCollectionForRequestNonPhpcrUrl()
    {
        $routes = $this->repository->getRouteCollectionForRequest(Request::create('http:///'));
        $this->assertInstanceOf(RouteCollection::class, $routes);
        $this->assertCount(1, $routes);

        $iterator = $routes->getIterator();

        $this->assertTrue($iterator->valid());
        $this->assertEquals(self::ROUTE_ROOT, $iterator->key());
    }

    /**
     * The root route will always be found.
     */
    public function testGetRouteCollectionForRequestColonInUrl()
    {
        $collection = $this->repository->getRouteCollectionForRequest(Request::create('http://foo.com/jcr:content'));
        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertCount(0, $collection);
    }

    public function testGetRoutesByNames()
    {
        $this->buildRoutes();

        $routeNames = [
            self::ROUTE_ROOT.'/testroute/noroute/child',
            self::ROUTE_ROOT.'/testroute/noroute',
            self::ROUTE_ROOT.'/testroute/', // trailing slash is invalid for phpcr
            self::ROUTE_ROOT.'/testroute',
        ];

        $routes = $this->repository->getRoutesByNames($routeNames);
        $this->assertCount(2, $routes);
        $this->assertContainsOnlyInstancesOf(RouteObjectInterface::class, $routes);
    }
}
