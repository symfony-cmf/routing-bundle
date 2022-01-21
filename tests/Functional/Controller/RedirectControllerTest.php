<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Controller;

use Symfony\Cmf\Bundle\RoutingBundle\Controller\RedirectController;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;

final class RedirectControllerTest extends BaseTestCase
{
    private const ROUTE_ROOT = '/test/routing';

    private RedirectController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->db('PHPCR')->createTestNode();
        $this->createRoute(self::ROUTE_ROOT);

        $router = self::getContainer()->get('router');
        $this->controller = new RedirectController($router);
    }

    public function testRedirectUri(): void
    {
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

        $redirect = new RedirectRoute();
        $redirect->setPosition($root, 'redirectUri');
        $redirect->setUri('http://example.com/test-url');
        $redirect->setParameters(['test' => 7]); // parameters should be ignored in this case
        $redirect->setPermanent(true);
        $this->getDm()->persist($redirect);

        $this->getDm()->flush();

        $this->getDm()->clear();

        $redirect = $this->getDm()->find(null, self::ROUTE_ROOT.'/redirectUri');
        $response = $this->controller->redirectAction($redirect);

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://example.com/test-url', $response->getTargetUrl());
    }

    public function testRedirectContent(): void
    {
        $content = $this->createContent();

        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

        $route = new Route();
        $route->setContent($content);
        $route->setPosition($root, 'testroute');
        $this->getDm()->persist($route);

        $redirect = new RedirectRoute();
        $redirect->setPosition($root, 'redirectContent');
        $redirect->setRouteTarget($route);
        $redirect->setParameters(['test' => 'content']);
        $this->getDm()->persist($redirect);

        $this->getDm()->flush();

        $this->getDm()->clear();

        $redirect = $this->getDm()->find(null, self::ROUTE_ROOT.'/redirectContent');
        $response = $this->controller->redirectAction($redirect);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/testroute?test=content', $response->getTargetUrl());
    }

    public function testRedirectName(): void
    {
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

        $redirect = new RedirectRoute();
        $redirect->setPosition($root, 'redirectName');
        $redirect->setRouteName('symfony_route');
        $redirect->setParameters(['param' => 7]); // parameters should be ignored in this case
        $this->getDm()->persist($redirect);

        $this->getDm()->flush();

        $this->getDm()->clear();

        $redirect = $this->getDm()->find(null, self::ROUTE_ROOT.'/redirectName');
        $response = $this->controller->redirectAction($redirect);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/symfony_route_test?param=7', $response->getTargetUrl());
    }
}
