<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\Document\Generic;
use PHPCR\Util\PathHelper;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RouteProvider;

use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;

class RouteRepositoryTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    /** @var RouteProvider */
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->db('PHPCR')->createTestNode();
        $this->createRoute(self::ROUTE_ROOT);
        $this->repository = $this->getContainer()->get('cmf_routing.route_provider');
    }

    public function testGetRouteCollectionForRequest()
    {
        $route = new Route;
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

        $route->setPosition($root, 'testroute');
        $this->getDm()->persist($route);

        // smuggle a non-route thing into the repository
        $noroute = new Generic;
        $noroute->setParent($route);
        $noroute->setNodename('noroute');
        $this->getDm()->persist($noroute);

        $childroute = new Route;
        $childroute->setPosition($noroute, 'child');
        $this->getDm()->persist($childroute);

        $this->getDm()->flush();

        $this->getDm()->clear();

        $routes = $this->repository->getRouteCollectionForRequest(Request::create('/testroute/noroute/child'));
        $this->assertCount(3, $routes);

        foreach ($routes as $route) {
            $this->assertInstanceOf('Symfony\\Cmf\\Component\\Routing\\RouteObjectInterface', $route);
        }
    }

    public function testFindNophpcrUrl()
    {
        $collection = $this->repository->getRouteCollectionForRequest(Request::create(':///'));
        $this->assertInstanceOf('Symfony\\Component\\Routing\\RouteCollection', $collection);
        $this->assertCount(0, $collection);
    }

    public function testSetPrefix()
    {
        $this->repository->setPrefix(self::ROUTE_ROOT);
    }
}
