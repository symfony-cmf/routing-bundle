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

    public function setUp()
    {
        $this->setupBeforeClass(array(), basename(self::ROUTE_ROOT));
        self::$manager = self::$kernel->getContainer()->get('symfony_cmf_routing_extra.auto_route_manager');

    }

    protected function createPost()
    {
        $post = new Post;
        $post->path = '/test/test-post';
        $post->title = 'Unit testing blog post';

        self::$dm->persist($post);
        self::$dm->flush();
        self::$dm->clear();
    }

    public function testPersist()
    {
        $this->createPost();

        $route = self::$dm->find(null, '/test/routing/unit-testing-blog-post');

        $this->assertNotNull($route);

        // make sure auto-route has been persisted
        $post = self::$dm->find(null, '/test/test-post');
        $routes = self::$dm->getReferrers($post);

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $routes[0]);
        $this->assertEquals('unit-testing-blog-post', $routes[0]->getName());
    }

    public function testUpdate()
    {
        $this->createPost();

        $post = self::$dm->find(null, '/test/test-post');
        // test update
        $post->title = 'Foobar';
        self::$dm->persist($post);
        self::$dm->flush();

        // make sure auto-route has been persisted
        $post = self::$dm->find(null, '/test/test-post');
        $routes = $post->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $routes[0]);

        self::$dm->refresh($routes[0]);

        $this->assertEquals('foobar', $routes[0]->getName());
        $this->assertEquals('/test/routing/foobar', $routes[0]->getId());

    }

    public function testRemove()
    {
        $this->createPost();
        $post = self::$dm->find(null, '/test/test-post');

        // test removing
        self::$dm->remove($post);

        self::$dm->flush();

        $baseRoute = self::$dm->find(null, '/test'.self::ROUTE_ROOT);
        $routes = self::$dm->getChildren($baseRoute);
        $this->assertCount(0, $routes);
    }
}
