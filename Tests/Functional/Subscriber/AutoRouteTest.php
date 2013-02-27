<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\Subscriber;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\Testdoc\Post;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\BaseTestCase;

class AutoRouteTest extends BaseTestCase
{
    /**
     * @var \Symfony\Cmf\Component\Routing\ChainRouter
     */
    protected static $manager;
    protected static $routeRepo;

    const ROUTE_ROOT = '/routing';

    public static function setupBeforeClass(array $options = array(), $routebase = null)
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
        self::$manager = self::$kernel->getContainer()->get('symfony_cmf_routing_extra.auto_route_manager');

    }

    public function testSubscriber()
    {
        $post = new Post;
        $post->path = '/functional/test-post';
        $post->title = 'Unit testing blog post';
        self::$dm->persist($post);
        self::$dm->flush();

        self::$dm->refresh($post);

        $route = self::$dm->find(null, '/functional/routing/unit-testing-blog-post');

        $this->assertNotNull($route);

        // make sure auto-route has been persisted
        $post = self::$dm->find(null, '/functional/test-post');
        $routes = self::$dm->getReferrers($post);

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $routes[0]);
        $this->assertEquals('unit-testing-blog-post', $routes[0]);

        // test update
        $post->title = 'Foobar';
        self::$dm->persist($post);
        self::$dm->flush();

        // make sure auto-route has been persisted
        $post = self::$dm->find(null, '/functional/test-post');
        $routes = $post->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $routes[0]);

        // uncomment this for an infinite loop
        // self::$dm->refresh($routes[0]);

        $this->assertEquals('foobar', $routes[0]);
        // $this->assertEquals('/functional/routing/foobar', $routes[0]->getId());

        // test removing
        self::$dm->remove($post);

        self::$dm->flush();

        $baseRoute = self::$dm->find(null, '/functional'.self::ROUTE_ROOT);
        $routes = self::$dm->getChildren($baseRoute);
        $this->assertCount(0, $routes);
    }
}
