<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2015 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Orm;

use Symfony\Component\HttpFoundation\Request;

class RouteProviderTest extends OrmTestCase
{
    private $repository;

    protected function setUp()
    {
        $this->repository = $this->getContainer()->get('cmf_routing.route_provider');
    }

    public function testGetRouteCollectionForRequest()
    {
        $createdRoutes = array();
        $createdRoutes[] = $this->createRoute('route1', '/test');
        $createdRoutes[] = $this->createRoute('route2', '/test/child');
        $createdRoutes[] = $this->createRoute('route3', '/test/child/testroutechild');

        $routes = $this->repository->getRouteCollectionForRequest(Request::create('/test/child/testroutechild'));
        $this->assertCount(3, $routes);
        $this->assertContainsOnlyInstancesOf('Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route', $routes);

        // clean up
        $this->removeRoutes($createdRoutes);
    }
}
