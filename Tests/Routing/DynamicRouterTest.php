<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Tests\Routing;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;

use Symfony\Cmf\Bundle\RoutingExtraBundle\Routing\DynamicRouter;

use Symfony\Cmf\Component\Routing\Test\CmfUnitTestCase;

class DynamicRouterTest extends CmfUnitTestCase
{
    protected $matcher;
    protected $generator;
    /** @var DynamicRouter */
    protected $router;
    protected $context;
    /** @var Request */
    protected $request;
    protected $container;

    public function setUp()
    {
        $this->matcher = $this->buildMock('Symfony\\Component\\Routing\\Matcher\\UrlMatcherInterface');
        $this->matcher->expects($this->once())
            ->method('match')
            ->with('/foo')
            ->will($this->returnValue(array('foo' => 'bar', RouteObjectInterface::CONTENT_OBJECT => 'bla', RouteObjectInterface::TEMPLATE_NAME => 'template')))
        ;

        $this->generator = $this->buildMock('Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface');

        $this->request = Request::create('/foo');
        $this->container = $this->buildMock('Symfony\\Component\\DependencyInjection\\ContainerInterface');
        $this->context = $this->buildMock('Symfony\\Component\\Routing\\RequestContext');

        $this->router = new DynamicRouter($this->context, $this->matcher, $this->generator);
        $this->router->setContainer($this->container);
    }

    private function assertRequestAttributes($request)
    {
        $this->assertTrue($request->attributes->has(DynamicRouter::CONTENT_KEY));
        $this->assertEquals('bla', $request->attributes->get(DynamicRouter::CONTENT_KEY));
        $this->assertTrue($request->attributes->has(DynamicRouter::CONTENT_TEMPLATE));
        $this->assertEquals('template', $request->attributes->get(DynamicRouter::CONTENT_TEMPLATE));
    }

    public function testMatch()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request))
        ;

        $parameters = $this->router->match('/foo');
        $this->assertEquals(array('foo' => 'bar'), $parameters);

        $this->assertRequestAttributes($this->request);
    }

    public function testMatchRequest()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($this->request))
        ;

        $parameters = $this->router->matchRequest($this->request);
        $this->assertEquals(array('foo' => 'bar'), $parameters);

        $this->assertRequestAttributes($this->request);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchNoRequest()
    {
        $this->container->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue(null))
        ;

        $this->router->match('/foo');
    }
}
