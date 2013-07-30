<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Controller;

use PHPCR\Util\PathHelper;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\RedirectRoute;
use Symfony\Cmf\Bundle\RoutingBundle\Controller\RedirectController;
use Symfony\Cmf\Component\Testing\Functional\BaseTestCase;
use Symfony\Cmf\Component\Testing\Document\Content;
use PHPCR\Util\NodeHelper;

class RedirectControllerTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    /**
     * @var \Symfony\Cmf\Bundle\RoutingBundle\Controller\RedirectController
     */
    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->db('PHPCR')->createTestNode();
        $this->db('PHPCR')->createPath(self::ROUTE_ROOT);
        $this->dm = $this->db('PHPCR')->getOm();

        $router = $this->getContainer()->get('router');
        $this->controller = new RedirectController($router);
    }

    public function testRedirectUri()
    {
        $root = $this->dm->find(null, self::ROUTE_ROOT);

        $redirect = new RedirectRoute;
        $redirect->setPosition($root, 'redirectUri');
        $redirect->setUri('http://example.com/test-url');
        $redirect->setParameters(array('test' => 7)); // parameters should be ignored in this case
        $redirect->setPermanent(true);
        $this->dm->persist($redirect);

        $this->dm->flush();

        $this->dm->clear();

        $redirect = $this->dm->find(null, self::ROUTE_ROOT.'/redirectUri');
        $response = $this->controller->redirectAction($redirect);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://example.com/test-url', $response->getTargetUrl());
    }

    public function testRedirectContent()
    {
        $root = $this->dm->find(null, self::ROUTE_ROOT);

        $content = new Content;
        $content->setId('/test/content');
        $content->setTitle('Foo Content');
        $this->dm->persist($content);
        $this->dm->flush();

        $route = new Route;
        $route->setContent($content);
        $route->setPosition($root, 'testroute');
        $this->dm->persist($route);

        $redirect = new RedirectRoute;
        $redirect->setPosition($root, 'redirectContent');
        $redirect->setRouteTarget($route);
        $redirect->setParameters(array('test' => 'content'));
        $this->dm->persist($redirect);

        $this->dm->flush();

        $this->dm->clear();

        $redirect = $this->dm->find(null, self::ROUTE_ROOT.'/redirectContent');
        $response = $this->controller->redirectAction($redirect);

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/testroute?test=content', $response->getTargetUrl());
    }

    public function testRedirectName()
    {
        $root = $this->dm->find(null, self::ROUTE_ROOT);

        $redirect = new RedirectRoute;
        $redirect->setPosition($root, 'redirectName');
        $redirect->setRouteName('symfony_route');
        $redirect->setParameters(array('param'=>7)); // parameters should be ignored in this case
        $this->dm->persist($redirect);

        $this->dm->flush();

        $this->dm->clear();

        $redirect = $this->dm->find(null, self::ROUTE_ROOT.'/redirectName');
        $response = $this->controller->redirectAction($redirect);
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\RedirectResponse', $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/symfony_route_test?param=7', $response->getTargetUrl());
    }
}
