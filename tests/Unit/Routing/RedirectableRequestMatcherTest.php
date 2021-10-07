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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Cmf\Bundle\RoutingBundle\Routing\RedirectableRequestMatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;

class RedirectableRequestMatcherTest extends TestCase
{
    /**
     * @var RedirectableRequestMatcher
     */
    private $redirectableRequestMatcher;

    /**
     * @var RequestMatcherInterface|MockObject
     */
    private $decoratedRequestMatcher;

    /**
     * @var Request
     */
    private $requestWithoutSlash;

    /**
     * @var Request
     */
    private $requestWithSlash;

    /**
     * @var RequestContext|MockObject
     */
    private $context;

    public function setUp(): void
    {
        $this->requestWithoutSlash = Request::create('/foo');
        $this->requestWithSlash = Request::create('/foo/');
        $this->decoratedRequestMatcher = $this->createMock(RequestMatcherInterface::class);
        $this->context = $this->createMock(RequestContext::class);
        $this->redirectableRequestMatcher = new RedirectableRequestMatcher($this->decoratedRequestMatcher, $this->context);
    }

    public function testMatchRequest()
    {
        $this->decoratedRequestMatcher
            ->expects($this->once())
            ->method('matchRequest')
            ->with($this->requestWithoutSlash)
            ->will($this->returnValue(['foo' => 'bar']));

        $parameters = $this->redirectableRequestMatcher->matchRequest($this->requestWithoutSlash);
        $this->assertEquals(['foo' => 'bar'], $parameters);
    }

    public function testMatchRequestWithSlash()
    {
        $this->decoratedRequestMatcher
            ->method('matchRequest')
            ->withConsecutive([$this->callback(function (Request $request) {
                return '/foo/' === $request->getPathInfo();
            })], [$this->callback(function (Request $request) {
                return '/foo' === $request->getPathInfo();
            })])
            ->will($this->onConsecutiveCalls(
                $this->throwException(new ResourceNotFoundException()),
                $this->returnValue(['_route' => 'foobar'])
            ));

        $parameters = $this->redirectableRequestMatcher->matchRequest($this->requestWithSlash);
        $this->assertTrue('foobar' === $parameters['_route']);
        $this->assertTrue('/foo' === $parameters['path']);
    }
}
