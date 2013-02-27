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

    const ROUTE_ROOT = '/test/routing';

    public static function setupBeforeClass(array $options = array(), $routebase = null)
    {
        parent::setupBeforeClass(array(), basename(self::ROUTE_ROOT));
        self::$manager = self::$kernel->getContainer()->get('symfony_cmf_routing_extra.auto_route_manager');
        if ($document = self::$dm->find(null, '/test-post')) {
            self::$dm->remove($document);
            self::$dm->flush();
        }
    }

    public function testSubscriber()
    {
        $post = new Post;
        $post->path = '/test-post';
        $post->title = 'Unit testing blog post';
        self::$dm->persist($post);
        self::$dm->flush();
<<<<<<< Updated upstream
        self::$dm->flush();
        self::$dm->clear();
=======
>>>>>>> Stashed changes

        // make sure auto-route has been persisted
        $post = self::$dm->find(null, '/test-post');
        $routes = $post->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $routes[0]);
        $this->assertEquals('unit-testing-blog-post', $routes[0]);

        // test update
        $post->title = 'Foobar';
        self::$dm->persist($post);
        self::$dm->flush();
<<<<<<< Updated upstream
        self::$dm->clear();
=======
>>>>>>> Stashed changes

        // make sure auto-route has been persisted
        $post = self::$dm->find(null, '/test-post');
        $routes = $post->routes;

<<<<<<< Updated upstream
        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $routes[0]);
        $this->assertEquals('foobar', $routes[0]);
        $this->assertEquals('/test/routing/foobar', $route->getId());

        // test removing
        self::$dm->remove($post);

        self::$dm->flush();
        self::$dm->clear();

        $baseRoute = $this->dm->find(null, '/test/'.self::ROUTE_ROOT);
        $routes = $this->dm->getChildren($baseRoute);
        $this->assertCount(0, $routes);
=======
        //$this->assertCount(1, $routes);
        //$this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $routes[0]);
        //$this->assertEquals('foobar', $routes[0]);
        //$this->assertEquals('/test/routing/foobar', $route->getId());

        //// test removing
        //self::$dm->remove($post);

        //self::$dm->flush();
        //self::$dm->clear();

        //$baseRoute = $this->dm->find(null, '/test/'.self::ROUTE_ROOT);
        //$routes = $this->dm->getChildren($baseRoute);
        //$this->assertCount(0, $routes);
>>>>>>> Stashed changes
    }
}
