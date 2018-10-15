<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;
use Symfony\Cmf\Component\Routing\RedirectRouteInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Model\RedirectRoute;

class RedirectRouteTest extends OrmTestCase
{
    private $repository;

    public function setUp()
    {
        parent::setUp();
        $this->clearDb(Route::class);

        $this->repository = $this->getContainer()->get('cmf_routing.route_provider');
    }

    public function testRedirectDoctrine()
    {
        $route = $this->createRoute('route1', '/test');

        $redirectRouteDoc = new RedirectRoute();
        $redirectRouteDoc->setRouteName("redirect1");
        $redirectRouteDoc->setRouteTarget($route);
        $redirectRouteDoc->setPermanent(true);

        $this->getDm()->flush();

        $redirectRoute = new Route();
        $redirectRoute->setName("redirect1");
        $redirectRoute->setStaticPrefix('/redirect');
        $redirectRoute->setContent($redirectRouteDoc);

        $this->getDm()->flush();
        $this->getDm()->clear();

        $route1 = $this->repository->getRouteByName('route1');
        $redirect1 = $this->repository->getRouteByName('redirect1');
        $this->assertInstanceOf(Route::class, $route1);
        $this->assertInstanceOf(RedirectRouteInterface::class, $redirect1);

        $this->assertSame($route1, $redirect1->getContent()->getRouteTarget());
    }

}
