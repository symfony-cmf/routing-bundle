<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\Document;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\Route;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Document\RedirectRoute;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\BaseTestCase;

class RedirectRouteTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/redirectroute';

    public static function setupBeforeClass(array $options = array(), $routebase = null)
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
    }

    public function testRedirectDoctrine()
    {
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route = new Route;
        $route->setRouteContent($root); // this happens to be a referenceable node
        $route->setPosition($root, 'testroute');
        self::$dm->persist($route);

        $redirect = new RedirectRoute;
        $redirect->setPosition($root, 'redirect');
        $redirect->setRouteTarget($route);
        $redirect->setDefault('test', 'toast');
        self::$dm->persist($redirect);

        self::$dm->flush();

        self::$dm->clear();

        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute');
        $redirect = self::$dm->find(null, self::ROUTE_ROOT.'/redirect');

        $this->assertInstanceOf('Symfony\\Cmf\\Component\\Routing\\RedirectRouteInterface', $redirect);
        $this->assertSame($redirect, $redirect->getRouteContent());
        $params = $redirect->getParameters();
        $this->assertArrayHasKey('route', $params);
        $this->assertSame($route, $params['route']);
        $defaults = $redirect->getDefaults();
        $this->assertEquals(array('test' => 'toast'), $defaults);
    }

    /**
     * @expectedException LogicException
     */
    public function testSetContent()
    {
        $content = $this->getMock('Symfony\\Cmf\\Component\\Routing\\RouteAwareInterface');
        $redirect = new RedirectRoute;
        $redirect->setRouteContent($content);
    }
}
