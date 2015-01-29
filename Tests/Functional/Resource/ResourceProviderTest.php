<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Resource;

use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Component\HttpFoundation\Request;

class ResourceProviderTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    protected function getKernelConfiguration()
    {
        return array(
            'environment' => 'resource',
        );
    }

    /**
     * @var ResourceProvider
     */
    private $provider;

    public function setUp()
    {
        parent::setUp();
        $this->db('PHPCR')->createTestNode();
        $this->createRoute(self::ROUTE_ROOT);

        $this->provider = $this->getContainer()->get('cmf_routing.route_provider');
    }

    private function buildRoutes()
    {
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

        $route = new Route;
        $route->setPosition($root, 'testroute');
        $route->setDefault('_format', 'html');
        $this->getDm()->persist($route);

        // smuggle a non-route thing into the repository
        $noroute = new Generic;
        $noroute->setParent($route);
        $noroute->setNodename('noroute');
        $this->getDm()->persist($noroute);

        $childroute = new Route;
        $childroute->setPosition($noroute, 'child');
        $childroute->setDefault('_format', 'json');
        $this->getDm()->persist($childroute);

        $this->getDm()->flush();
        $this->getDm()->clear();
    }

    public function testGetRouteCollectionForRequest()
    {
        $this->buildRoutes();

        $routeCollection = $this->provider->getRouteCollectionForRequest(Request::create('/testroute/noroute/child'));
        $this->assertCount(3, $routeCollection);
        $this->assertContainsOnlyInstancesOf('Symfony\Cmf\Component\Routing\RouteObjectInterface', $routeCollection);

        $routes = $routeCollection->all();
        list($key, $child) = each($routes);
        $this->assertEquals('/testroute/noroute/child', $key);
        $this->assertEquals('json', $child->getDefault('_format'));
        list($key, $testroute) = each($routes);
        $this->assertEquals('/testroute', $key);
        $this->assertEquals('html', $testroute->getDefault('_format'));
        list($key, $root) = each($routes);
        $this->assertEquals('/', $key);
        $this->assertNull($root->getDefault('_format'));
    }

    public function testGetRouteCollectionForRequestFormat()
    {
        $this->buildRoutes();

        $routes = $this->provider->getRouteCollectionForRequest(Request::create('/testroute/noroute/child.html'));
        $this->assertCount(3, $routes);
        $this->assertContainsOnlyInstancesOf('Symfony\\Cmf\\Component\\Routing\\RouteObjectInterface', $routes);

        $routes = $routes->all();
        list($key, $child) = each($routes);
        $this->assertEquals('/testroute/noroute/child', $key);
        $this->assertEquals('json', $child->getDefault('_format'));
        list($key, $testroute) = each($routes);
        $this->assertEquals('/testroute', $key);
        $this->assertEquals('html', $testroute->getDefault('_format'));
        list($key, $root) = each($routes);
        $this->assertEquals('/', $key);
        $this->assertEquals(null, $root->getDefault('_format'));
    }

    /**
     * The root route will always be found.
     */
    public function testGetRouteCollectionForRequestNophpcrUrl()
    {
        // $collection = $this->provider->getRouteCollectionForRequest(Request::create(':///'));
        $collection = $this->provider->getRouteCollectionForRequest(Request::create('/asdasd'));
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $collection);
        $this->assertCount(1, $collection);
        $routes = $collection->all();
        list ($key, $route) = each($routes);
        $this->assertEquals('/', $key);
    }

    /**
     * TODO
     * @expectedException InvalidArgumentException
     */
    public function testGetRoutesByNames()
    {
        $this->buildRoutes();

        $routeNames = array(
            '/testroute/noroute/child',
            '/testroute/noroute',
            '/testroute/', // trailing slash is invalid for phpcr
            '/testroute'
        );

        $routes = $this->provider->getRoutesByNames($routeNames);
        $this->assertCount(2, $routes);
        $this->assertContainsOnlyInstancesOf('Symfony\Cmf\Component\Routing\RouteObjectInterface', $routes);
    }
}
