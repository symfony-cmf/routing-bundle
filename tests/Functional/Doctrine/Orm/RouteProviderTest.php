<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Component\HttpFoundation\Request;

class RouteProviderTest extends OrmTestCase
{
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->clearDb(Route::class);

        $this->repository = $this->getContainer()->get('cmf_routing.route_provider');
    }

    public function testGetRouteCollectionForRequest()
    {
        $this->createRoute('route1', '/test');
        $this->createRoute('route2', '/test/child');
        $this->createRoute('route3', '/test/child/testroutechild');

        $this->getDm()->clear();

        $routes = $this->repository->getRouteCollectionForRequest(Request::create('/test/child/testroutechild'));
        $this->assertCount(3, $routes);
        $this->assertContainsOnlyInstancesOf(Route::class, $routes);
    }
}
