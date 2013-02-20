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
    }

    public function testUpdateAutoRouteForDocument()
    {
        if ($document = self::$dm->find(null, '/test-post')) {
            self::$dm->remove($document);
            self::$dm->flush();
        }
        $post = new Post;
        $post->path = '/test-post';
        self::$dm->persist($post);

        $autoRoute = self::$manager->updateAutoRouteForDocument($post);

        $this->assertInstanceOf('Symfony\Cmf\Bundle\RoutingExtraBundle\Document\AutoRoute', $autoRoute);
        $this->assertEquals('unit-testing-blog-post', $autoRoute->getName());
    }
}

