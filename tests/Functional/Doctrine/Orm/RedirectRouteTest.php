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

use Symfony\Cmf\Bundle\RoutingBundle\Controller\RedirectController;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\RedirectRoute;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;

class RedirectRouteTest extends OrmTestCase
{
    private RedirectController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clearDb(Route::class);
        $this->clearDb(RedirectRoute::class);

        $this->controller = new RedirectController(self::getContainer()->get('router'));
    }

    public function testRedirectDoctrine(): void
    {
        $route = $this->createRoute('route1', '/test');

        $redirectRouteDoc = new RedirectRoute();
        $redirectRouteDoc->setRouteName('redirect1');
        $redirectRouteDoc->setRouteTarget($route);
        $redirectRouteDoc->setPermanent(true);

        $this->getDm()->persist($redirectRouteDoc);
        $this->getDm()->flush();

        $redirectRoute = $this->createRoute('redirect1', '/redirect');
        $redirectRoute->setContent($redirectRouteDoc);

        $this->getDm()->flush();
        $this->getDm()->clear();

        $response = $this->controller->redirectAction($redirectRoute->getContent());

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://localhost/test', $response->getTargetUrl());
    }
}
