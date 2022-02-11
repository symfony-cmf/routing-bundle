<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Doctrine\Phpcr;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;

class RouteTest extends TestCase
{
    private Route $route;

    private Route $childRoute1;

    public function setUp(): void
    {
        $this->route = new Route();

        $this->childRoute1 = new Route();
        $this->childRoute1->setName('child route1');
    }

    public function testGetRouteChildren(): void
    {
        $refl = new \ReflectionClass($this->route);
        $prop = $refl->getProperty('children');
        $prop->setAccessible(true);
        $prop->setValue($this->route, new ArrayCollection([
            new \stdClass(),
            $this->childRoute1,
        ]));

        $res = $this->route->getRouteChildren();
        $this->assertCount(1, $res);
        $this->assertEquals('child route1', $res[0]->getName());
    }

    public function testGetRouteChildrenNull(): void
    {
        $res = $this->route->getRouteChildren();
        $this->assertEquals([], $res);
    }
}
