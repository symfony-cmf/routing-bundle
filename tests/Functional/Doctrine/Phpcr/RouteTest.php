<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Tests\Functional\BaseTestCase;

class RouteTest extends BaseTestCase
{
    const ROUTE_ROOT = '/test/routing';

    public function setUp(): void
    {
        parent::setUp();
        $this->db('PHPCR')->createTestNode();
        $this->createRoute(self::ROUTE_ROOT);
    }

    public function testPersist(): void
    {
        $route = new Route();
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

        $route->setContent($root); // this happens to be a referenceable node
        $route->setPosition($root, 'testroute');
        $route->setDefault('x', 'y');
        $route->setRequirement('testreq', 'testregex');
        $route->setOptions(['test' => 'value']);
        $route->setOption('another', 'value2');

        $this->getDm()->persist($route);
        $this->getDm()->flush();
        $this->assertEquals('/testroute', $route->getPath());

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

    public function testPersistEmptyOptions(): ?object
    {
        $route = new Route();
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

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
        $this->assertTrue(1 >= \count($options)); // there is a default option for the compiler

        return $route;
    }

    public function testConditionOption(): void
    {
        $route = new Route();
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

        $route->setPosition($root, 'conditionroute');
        $route->setCondition('foobar');

        $this->getDm()->persist($route);
        $this->getDm()->flush();
        $this->getDm()->clear();

        $route = $this->getDm()->find(null, self::ROUTE_ROOT.'/conditionroute');

        $this->assertEquals('foobar', $route->getCondition());
    }

    public function testRootRoute(): void
    {
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);
        $this->assertEquals('/', $root->getPath());
    }

    public function testSetPath(): void
    {
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);
        $this->assertEquals('/', $root->getStaticPrefix());
        $root->setPath('/{test}');
        $this->assertEquals('{test}', $root->getVariablePattern());
    }

    public function testSetPattern(): void
    {
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);
        $root->setVariablePattern('{test}');
        $this->assertEquals('/{test}', $root->getPath());
        $this->assertEquals('{test}', $root->getVariablePattern());
    }

    /**
     * @depends testPersistEmptyOptions
     */
    public function testSetPatternInvalid(Route $route): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $route->setPath('/impossible');
    }

    public function testInvalidIdPrefix(): void
    {
        $root = $this->getDm()->find(null, self::ROUTE_ROOT);
        $root->setPrefix('/changed'); // simulate a problem with the prefix setter listener
        $this->expectException(\LogicException::class);
        $this->assertEquals('/', $root->getPath());
    }

    public function testPrefixNonpersisted(): void
    {
        $route = new Route();
        $this->expectException(\LogicException::class);
        $route->getPath();
    }

    public function testDefaultFormat(): void
    {
        $route = new Route(['add_format_pattern' => true]);

        $root = $this->getDm()->find(null, self::ROUTE_ROOT);

        $route->setPosition($root, 'format');
        $this->getDm()->persist($route);
        $this->getDm()->flush();

        $this->getDm()->clear();

        $route = $this->getDm()->find(null, self::ROUTE_ROOT.'/format');

        $this->assertEquals('/format.{_format}', $route->getPath());
    }
}
