<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteTest extends OrmTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->clearDb(Route::class);

        $this->createRoute('root', '/test/');
    }

    public function testPersist()
    {
        $route = $this->createRoute('test', '/test/testroute', [], false);
        $linkedRoute = $this->getDm()->getRepository(Route::class)->findByPath('/test/');

        $route->setContent($linkedRoute); // this happens to be a referenceable node
        $route->setPosition(87);
        $route->setDefault('x', 'y');
        $route->setRequirement('testreq', 'testregex');
        $route->setOptions(['test' => 'value']);
        $route->setOption('another', 'value2');


        $this->getDm()->persist($route);
        $this->getDm()->flush();

        $this->assertEquals('test', $route->getName());

        $this->getDm()->clear();

        $route = $this->getDm()->getRepository(Route::class)->findByPath('/test/testroute');

        $this->assertNotNull($route->getContent());
        $this->assertEquals('/test/testroute', $route->getPath());

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
        $this->createRoute('testroute', '/test/empty');

        $route = $this->getDm()->getRepository(Route::class)->findByPath('/test/empty');

        $defaults = $route->getDefaults();
        $this->assertCount(0, $defaults);

        $requirements = $route->getRequirements();
        $this->assertCount(0, $requirements);

        $options = $route->getOptions();
        $this->assertTrue(1 >= count($options)); // there is a default option for the compiler

        return $route;
    }


    public function testConditionOption()
    {
        $route = $this->createRoute('testroute', '/test/conditionroute', [], false);
        $route->setCondition('foobar');

        $this->getDm()->persist($route);
        $this->getDm()->flush();
        $this->getDm()->clear();

        $route = $this->getDm()->getRepository(Route::class)->findByPath('/test/conditionroute');

        $this->assertEquals('foobar', $route->getCondition());
    }

    public function testRootRoute()
    {
        $root = $this->getDm()->getRepository(Route::class)->findByPath('/test/');
        $this->assertEquals('/test/', $root->getPath());
    }

    public function testSetPath()
    {
        $root = $this->getDm()->getRepository(Route::class)->findByPath('/test/');
        $this->assertEquals('/test/', $root->getStaticPrefix());
        $root->setPath('/test/{test}');
        $this->assertEquals('{test}', $root->getVariablePattern());
    }

    public function testSetPattern()
    {
        $root = $this->getDm()->getRepository(Route::class)->findByPath('/test');
        $root->setVariablePattern('{test}');
        $this->assertEquals('/test/{test}', $root->getPath());
        $this->assertEquals('{test}', $root->getVariablePattern());
    }

    public function testCreateVariablePattern()
    {
        $this->createRoute('test', '/test/{test}');

        $route = $this->getDm()->getRepository(Route::class)->findByPath('/test/{test}');
        $this->assertEquals('/test/{test}', $route->getPath());
        $this->assertEquals('{test}', $route->getVariablePattern());
    }

    /**
     * @depends testPersistEmptyOptions
     *
     * @expectedException \InvalidArgumentException
     */
    public function testSetPatternInvalid(Route $route)
    {
        $route->setPath('/test/impossible');
    }

    /**
     * @expectedException \LogicException
     */
    public function testInvalidIdPrefix()
    {
        $root = $this->getDm()->getRepository(Route::class)->findByPath('/test/');
        $root->setPrefix('/test/changed'); // simulate a problem with the prefix setter listener
        $this->assertEquals('/test/', $root->getPath());
    }

    /**
     * @expectedException \LogicException
     */
    public function testPrefixNonpersisted()
    {
        $route = new Route();
        $route->getPath();
    }

    public function testDefaultFormat()
    {
        # $route = new Route(['add_format_pattern' => true]);
        $this->createRoute('test', '/test/format');

        $route = $this->getDm()->getRepository(Route::class)->findByPath('/test/format');

        $this->assertEquals('/test/format.{_format}', $route->getPath());
    }
}
