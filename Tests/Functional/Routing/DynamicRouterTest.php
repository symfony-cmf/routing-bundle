<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Routing;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Bundle\RoutingBundle\Document\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Document\RedirectRoute;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Testdoc\Content;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;

/**
 * The goal of these tests is to test the interoperation with DI and everything.
 * We do not aim to cover all edge cases and exceptions - that is was the unit
 * test is here for.
 */
class DynamicRouterTest extends BaseTestCase
{
    /**
     * @var \Symfony\Cmf\Component\Routing\ChainRouter
     */
    protected static $router;
    protected static $routeNamePrefix;

    const ROUTE_ROOT = '/test/routing';

    public static function setupBeforeClass(array $options = array(), $routebase = null)
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
        self::$router = self::$kernel->getContainer()->get('router');

        $root = self::$dm->find(null, self::ROUTE_ROOT);

        // do not set a content here, or we need a valid request and so on...
        $route = new Route;
        $route->setPosition($root, 'testroute');
        $route->setVariablePattern('/{slug}/{id}');
        $route->setDefault('id', '0');
        $route->setRequirement('id', '[0-9]+');
        $route->setDefault(RouteObjectInterface::CONTROLLER_NAME, 'testController');
        // TODO: what are the options used for? we should test them too if it makes sense
        self::$dm->persist($route);

        $childroute = new Route;
        $childroute->setPosition($route, 'child');
        $childroute->setDefault(RouteObjectInterface::CONTROLLER_NAME, 'testController');
        self::$dm->persist($childroute);

        $formatroute = new Route(true);
        $formatroute->setPosition($root, 'format');
        $formatroute->setVariablePattern('/{id}');
        $formatroute->setRequirement('_format', 'html|json');
        $formatroute->setDefault(RouteObjectInterface::CONTROLLER_NAME, 'testController');
        self::$dm->persist($formatroute);

        self::$dm->flush();
    }

    public function testMatch()
    {
        $expected = array(
            RouteObjectInterface::CONTROLLER_NAME,
            RouteObjectInterface::ROUTE_NAME,
        );

        $request = Request::create('/testroute/child');
        $matches = self::$router->matchRequest($request);
        ksort($matches);

        $this->assertTrue($request->attributes->has(DynamicRouter::ROUTE_KEY));
        $this->assertEquals($expected, array_keys($matches));
        $this->assertEquals('/test/routing/testroute/child', $matches[RouteObjectInterface::ROUTE_NAME]);
    }

    public function testMatchParameters()
    {
        $expected = array(
            RouteObjectInterface::CONTROLLER_NAME   => 'testController',
            RouteObjectInterface::ROUTE_NAME => '/test/routing/testroute',
            'id'          => '123',
            'slug'        => 'child',
        );

        $request = Request::create('/testroute/child/123');

        $matches = self::$router->matchRequest($request);
        $this->assertTrue($request->attributes->has(DynamicRouter::ROUTE_KEY));
        ksort($matches);

        $this->assertEquals($expected, $matches);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testNoMatch()
    {
        self::$router->matchRequest(Request::create('/testroute/child/123a'));
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\MethodNotAllowedException
     */
    public function testNotAllowed()
    {
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        // do not set a content here, or we need a valid request and so on...
        $route = new Route;
        $route->setPosition($root, 'notallowed');
        $route->setRequirement('_method', 'GET');
        $route->setDefault(RouteObjectInterface::CONTROLLER_NAME, 'testController');
        self::$dm->persist($route);
        self::$dm->flush();

        self::$router->matchRequest(Request::create('/notallowed', 'POST'));
    }

    public function testMatchDefaultFormat()
    {
        $expected = array(
            '_controller' => 'testController',
            '_format'     => 'html',
            RouteObjectInterface::ROUTE_NAME => '/test/routing/format',
            'id'          => '48',
        );
        $request = Request::create('/format/48');
        $matches = self::$router->matchRequest($request);
        ksort($matches);

        $this->assertTrue($request->attributes->has(DynamicRouter::ROUTE_KEY));
        $this->assertEquals($expected, $matches);
    }

    public function testMatchFormat()
    {
        $expected = array(
            '_controller' => 'testController',
            '_format'     => 'json',
            RouteObjectInterface::ROUTE_NAME => '/test/routing/format',
            'id'          => '48',
        );
        $request = Request::create('/format/48.json');
        $matches = self::$router->matchRequest($request);
        ksort($matches);

        $this->assertTrue($request->attributes->has(DynamicRouter::ROUTE_KEY));
        $this->assertEquals($expected, $matches);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testNoMatchingFormat()
    {
        self::$router->matchRequest(Request::create('/format/48.xml'));
    }

    public function testEnhanceControllerByAlias()
    {
        // put a redirect route
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route = new RedirectRoute;
        $route->setDefault('type', 'demo_alias');
        $route->setPosition($root, 'controlleralias');
        self::$dm->persist($route);
        self::$dm->flush();

        $expected = array(
            '_controller' => 'test.controller:aliasAction',
            RouteObjectInterface::ROUTE_NAME => '/test/routing/controlleralias',
            'type'        => 'demo_alias',
        );
        $request = Request::create('/controlleralias');
        $matches = self::$router->matchRequest($request);
        ksort($matches);

        $this->assertTrue($request->attributes->has(DynamicRouter::ROUTE_KEY));
        $this->assertEquals($expected, $matches);
    }

    public function testEnhanceControllerByClass()
    {
        // put a redirect route
        $root = self::$dm->find(null, self::ROUTE_ROOT);

        $route = new RedirectRoute;
        $route->setRouteTarget($root);
        $route->setPosition($root, 'redirect');
        self::$dm->persist($route);
        self::$dm->flush();

        $expected = array(
            '_controller' => 'cmf_routing.redirect_controller:redirectAction',
            RouteObjectInterface::ROUTE_NAME => '/test/routing/redirect',
        );
        $request = Request::create('/redirect');
        $matches = self::$router->matchRequest($request);
        ksort($matches);

        $this->assertTrue($request->attributes->has(DynamicRouter::ROUTE_KEY));
        $this->assertEquals($expected, $matches);
    }

    public function testEnhanceTemplateByClass()
    {
        if ($content = self::$dm->find(null, '/templatebyclass')) {
            self::$dm->remove($content);
            self::$dm->flush();
        }
        $document = new Content();
        $document->path = '/templatebyclass';
        self::$dm->persist($document);

        // put a route for this content
        $root = self::$dm->find(null, self::ROUTE_ROOT);
        $route = new Route;
        $route->setRouteContent($document);
        $route->setPosition($root, 'templatebyclass');
        self::$dm->persist($route);
        self::$dm->flush();

        $expected = array(
            '_controller' => 'cmf_content.controller:indexAction',
            RouteObjectInterface::ROUTE_NAME => '/test/routing/templatebyclass',
        );
        $request = Request::create('/templatebyclass');
        $matches = self::$router->matchRequest($request);
        ksort($matches);

        $this->assertEquals($expected, $matches);
        $this->assertTrue($request->attributes->has(DynamicRouter::ROUTE_KEY));
        $this->assertTrue($request->attributes->has(DynamicRouter::CONTENT_TEMPLATE));
        $this->assertEquals('TestBundle:Content:index.html.twig', $request->attributes->get(DynamicRouter::CONTENT_TEMPLATE));
    }

    public function testGenerate()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute/child');

        $url = self::$router->generate($route, array('test' => 'value'));
        $this->assertEquals('/testroute/child?test=value', $url);
    }

    public function testGenerateAbsolute()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute/child');
        $url = self::$router->generate($route, array('test' => 'value'), true);
        $this->assertEquals('http://localhost/testroute/child?test=value', $url);
    }

    public function testGenerateParameters()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute');

        $url = self::$router->generate($route, array('slug' => 'gen-slug', 'test' => 'value'));
        $this->assertEquals('/testroute/gen-slug?test=value', $url);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function testGenerateParametersInvalid()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/testroute');

        self::$router->generate($route, array('slug' => 'gen-slug', 'id' => 'nonumber'));
    }

    public function testGenerateDefaultFormat()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/format');

        $url = self::$router->generate($route, array('id' => 37));
        $this->assertEquals('/format/37', $url);
    }

    public function testGenerateFormat()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/format');

        $url = self::$router->generate($route, array('id' => 37, '_format' => 'json'));
        $this->assertEquals('/format/37.json', $url);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function testGenerateNoMatchingFormat()
    {
        $route = self::$dm->find(null, self::ROUTE_ROOT.'/format');

        self::$router->generate($route, array('id' => 37, '_format' => 'xyz'));
    }
}
