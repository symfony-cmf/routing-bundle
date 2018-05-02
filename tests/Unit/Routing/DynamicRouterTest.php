<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\Routing;

use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;
use Symfony\Cmf\Component\Routing\Event\Events;
use Symfony\Cmf\Component\Routing\Event\RouterMatchEvent;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;

class DynamicRouterTest extends \PHPUnit_Framework_TestCase
{
    protected $matcher;

    protected $generator;

    /** @var DynamicRouter */
    protected $router;

    protected $context;

    /** @var Request */
    protected $request;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    protected $container;

    public function setUp()
    {
        $this->matcher = $this->createMock(UrlMatcherInterface::class);
        $this->matcher->expects($this->once())
            ->method('match')
            ->with('/foo')
            ->will($this->returnValue(['foo' => 'bar', RouteObjectInterface::CONTENT_OBJECT => 'bla', RouteObjectInterface::TEMPLATE_NAME => 'template']))
        ;

        $this->generator = $this->createMock(UrlGeneratorInterface::class);

        $this->request = Request::create('/foo');
        $this->requestStack = new RequestStack();
        $this->requestStack->push($this->request);
        $this->context = $this->createMock(RequestContext::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->router = new DynamicRouter($this->context, $this->matcher, $this->generator, '', $this->eventDispatcher);
        $this->router->setRequestStack($this->requestStack);
    }

    private function assertRequestAttributes($request)
    {
        $this->assertTrue($request->attributes->has(DynamicRouter::CONTENT_KEY));
        $this->assertEquals('bla', $request->attributes->get(DynamicRouter::CONTENT_KEY));
        $this->assertTrue($request->attributes->has(DynamicRouter::CONTENT_TEMPLATE));
        $this->assertEquals('template', $request->attributes->get(DynamicRouter::CONTENT_TEMPLATE));
    }

    /**
     * @group legacy
     */
    public function testMatch()
    {
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(Events::PRE_DYNAMIC_MATCH, $this->equalTo(new RouterMatchEvent()))
        ;

        $parameters = $this->router->match('/foo');
        $this->assertEquals(['foo' => 'bar'], $parameters);

        $this->assertRequestAttributes($this->request);
    }

    public function testMatchRequest()
    {
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(Events::PRE_DYNAMIC_MATCH_REQUEST, $this->equalTo(new RouterMatchEvent($this->request)))
        ;

        $parameters = $this->router->matchRequest($this->request);
        $this->assertEquals(['foo' => 'bar'], $parameters);

        $this->assertRequestAttributes($this->request);
    }

    /**
     * @group legacy
     */
    public function testMatchNoRequest()
    {
        $this->router->setRequestStack(new RequestStack());

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(Events::PRE_DYNAMIC_MATCH, $this->equalTo(new RouterMatchEvent()))
        ;

        $this->expectException(ResourceNotFoundException::class);

        $this->router->match('/foo');
    }

    public function testEventOptional()
    {
        $router = new DynamicRouter($this->context, $this->matcher, $this->generator);

        $parameters = $router->matchRequest($this->request);
        $this->assertEquals(['foo' => 'bar'], $parameters);

        $this->assertRequestAttributes($this->request);
    }
}
