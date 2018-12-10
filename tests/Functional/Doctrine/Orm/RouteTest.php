<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteTest extends OrmTestCase
{
    const ROUTE_ROOT = '/test/routing';

    public function setUp()
    {
        parent::setUp();
        $this->clearDb(Route::class);

        $this->repository = $this->getContainer()->get('cmf_routing.route_provider');

        $this->createRoute('root', self::ROUTE_ROOT);
    }

    public function testPersist()
    {
        $route = new Route();
        $root = $this->getDm()->find(Route::class, self::ROUTE_ROOT);

        $route->setContent($root); // this happens to be a referenceable node
        $route->setName('testroute');
        $route->setPosition(87);
        $route->setDefault('x', 'y');
        $route->setRequirement('testreq', 'testregex');
        $route->setOptions(['test' => 'value']);
        $route->setOption('another', 'value2');


        $this->getDm()->persist($route);
        $this->getDm()->flush();
        $this->assertEquals('testroute', $route->getName());

        $this->getDm()->clear();

        $route = $this->getDm()->find(null, self::ROUTE_ROOT.'/testroute');

        $this->assertNotNull($route->getContent());
        $this->assertEquals('/testroute', $route->getPath());

        $this->assertEquals('y', $route->getDefault('x'));
        $defaults = $route->getDefaults();
        $this->assertArrayHasKey('x', $defaults);
        $this->assertEquals('y', $defaults['x']);

        $requirements = $route->getRequirements();
        $this->assertArrayHasKey('testreq', $requirements);
        $this->assertEquals('testregex', $requirements['testreq']);

        $options = $route->getOptions();
        $this->assertArrayHasKey('test', $options);
        $this->assertEquals('value', $options['test']);
        $this->assertArrayHasKey('another', $options);
        $this->assertEquals('value2', $options['another']);
    }

    public function testPersistEmptyOptions()
    {
        $route = new Route();
        $root = $this->getDm()->find(Route::class, self::ROUTE_ROOT);

        $route->setPosition($root, 'empty');
        $this->getDm()->persist($route);
        $this->getDm()->flush();

        $this->getDm()->clear();

        $route = $this->getDm()->find(null, self::ROUTE_ROOT.'/empty');

        $defaults = $route->getDefaults();
        $this->assertCount(0, $defaults);

        $requirements = $route->getRequirements();
        $this->assertCount(0, $requirements);

        $options = $route->getOptions();
        $this->assertTrue(1 >= count($options)); // there is a default option for the compiler

        return $route;
    }
}
