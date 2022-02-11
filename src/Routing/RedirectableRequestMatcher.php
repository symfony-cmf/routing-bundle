<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ExceptionInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Reproduce the behaviour of the Symfony router to redirect paths with a trailing slash if necessary.
 *
 * The logic is taken from Symfony\Component\Routing\Matcher\RedirectableUrlMatcher and Symfony\Bundle\FrameworkBundle\Routing\RedirectableCompiledUrlMatcher
 *
 * @see https://symfony.com/doc/4.1/routing.html#routing-trailing-slash-redirection
 */
class RedirectableRequestMatcher implements RequestMatcherInterface
{
    private RequestMatcherInterface $decorated;

    private RequestContext $requestContext;

    public function __construct(RequestMatcherInterface $decorated, RequestContext $requestContext)
    {
        $this->decorated = $decorated;
        $this->requestContext = $requestContext;
    }

    /**
     * If the matcher can not find a match,
     * it will try to add or remove a trailing slash to the path and match again.
     */
    public function matchRequest(Request $request): array
    {
        try {
            return $this->decorated->matchRequest($request);
        } catch (ResourceNotFoundException $e) {
            if (!\in_array($request->getMethod(), ['HEAD', 'GET'], true)) {
                throw $e;
            }

            $pathinfo = $request->getPathInfo();

            if ('/' === $trimmedPathinfo = rtrim($pathinfo, '/') ?: '/') {
                throw $e;
            }

            $pathinfo = $trimmedPathinfo === $pathinfo ? $pathinfo.'/' : $trimmedPathinfo;

            try {
                $parameters = $this->decorated->matchRequest($this->rebuildRequest($request, $pathinfo));
            } catch (ExceptionInterface $e2) {
                // ignore $e2 and throw original exception
                throw $e;
            }

            return $this->redirect($pathinfo, $parameters['_route'] ?? null, $this->requestContext->getScheme()) + $parameters;
        }
    }

    private function redirect(string $path, string $route, string $scheme = null): array
    {
        return [
            '_controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction',
            'path' => $path,
            'permanent' => true,
            'scheme' => $scheme,
            'httpPort' => $this->requestContext->getHttpPort(),
            'httpsPort' => $this->requestContext->getHttpsPort(),
            '_route' => $route,
        ];
    }

    /**
     * Return a duplicated version of $request with the new $newpath as request_uri.
     */
    private function rebuildRequest(Request $request, string $newPath): Request
    {
        $server = $request->server->all();
        $server['REQUEST_URI'] = $newPath;

        return $request->duplicate(null, null, null, null, null, $server);
    }
}
