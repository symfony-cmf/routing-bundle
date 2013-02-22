<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\Routing;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\Testdoc\Post;
use Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Functional\BaseTestCase;

class AutoRouteManagerTest extends BaseTestCase
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

    public function testUpdateAutoRouteForDocumentWithNew()
    {
        $post = new Post;
        $post->path = '/test-post';
        $post->title = 'Unit testing blog post';
        self::$dm->persist($post);

        // test return value
        $autoRoute = self::$manager->updateAutoRouteForDocument($post);

        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $autoRoute);
        $this->assertEquals('unit-testing-blog-post', $autoRoute->getName());

        self::$dm->flush();
        self::$dm->clear();

        // make sure auto-route has been persisted
        $post = self::$dm->find(null, '/test-post');
        $routes = $post->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $routes[0]);
        $this->assertEquals('unit-testing-blog-post', $routes[0]);

        // test update
        $post->title = 'Foobar';
        self::$manager->updateAutoRouteForDocument($post);

        self::$dm->flush();
        self::$dm->clear();

        // make sure auto-route has been persisted
        $post = self::$dm->find(null, '/test-post');
        $routes = $post->routes;

        $this->assertCount(1, $routes);
        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $routes[0]);
        $this->assertEquals('foobar', $routes[0]);

        // test removing
        $removedRoutes = self::$manager->removeAutoRoutesForDocument($post);
        $this->assertCount(1, $routes);

        self::$dm->flush();
        self::$dm->clear();

        $post = self::$dm->find(null, '/test-post');
        $routes = $post->routes;
        $this->assertCount(0, $routes);
    }
}
