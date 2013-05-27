<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Document;

use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Bundle\RoutingBundle\Document\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Document\RouteProvider;

use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;

class RouteRepositoryTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    /** @var RouteProvider */
    private static $repository;

    public static function setupBeforeClass(array $options = array(), $routebase = null)
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
        self::$repository = self::$kernel->getContainer()->get('cmf_routing.route_provider');
    }

    public function testGetRouteCollectionForRequest()
    {
        $route = new Route;
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route->setPosition($root, 'testroute');
        self::$dm->persist($route);

        // smuggle a non-route thing into the repository
        $noroute = new Generic;
        $noroute->setParent($route);
        $noroute->setNodename('noroute');
        self::$dm->persist($noroute);

        $childroute = new Route;
        $childroute->setPosition($noroute, 'child');
        self::$dm->persist($childroute);

        self::$dm->flush();

        self::$dm->clear();

        $routes = self::$repository->getRouteCollectionForRequest(Request::create('/testroute/noroute/child'));
        $this->assertCount(3, $routes);

        foreach ($routes as $route) {
            $this->assertInstanceOf('Symfony\\Cmf\\Component\\Routing\\RouteObjectInterface', $route);
        }
    }

    public function testFindNophpcrUrl()
    {
        $collection = self::$repository->getRouteCollectionForRequest(Request::create(':///'));
        $this->assertInstanceOf('Symfony\\Component\\Routing\\RouteCollection', $collection);
        $this->assertCount(0, $collection);
    }

    public function testSetPrefix()
    {
        self::$repository->setPrefix(self::ROUTE_ROOT);
    }
}
