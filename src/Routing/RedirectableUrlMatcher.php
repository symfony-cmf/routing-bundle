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
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;

class RedirectableUrlMatcher implements RequestMatcherInterface, UrlGeneratorInterface, RedirectableUrlMatcherInterface
{
    /**
     * @var RequestMatcherInterface|UrlGeneratorInterface
     */
    private RequestMatcherInterface $router;

    public function __construct(RequestMatcherInterface $router)
    {
        if (false === $router instanceof UrlGeneratorInterface) {
            throw new \InvalidArgumentException('$router must implement UrlGeneratorInterface');
        }

        $this->router = $router;
    }

    /**
     * If the matcher can not find a match,
     * it will try to add or remove a trailing slash to the path and match again.
     */
    public function matchRequest(Request $request)
    {
        try {
            return $this->router->matchRequest($request);
        } catch (ResourceNotFoundException $e) {
            if (!\in_array($this->getContext()->getMethod(), ['HEAD', 'GET'], true)) {
                throw $e;
            }

            $pathinfo = $request->getPathInfo();

            if ('/' === $trimmedPathinfo = rtrim($pathinfo, '/') ?: '/') {
                throw $e;
            }

            $pathinfo = $trimmedPathinfo === $pathinfo ? $pathinfo.'/' : $trimmedPathinfo;

            $parameters = $this->router->matchRequest($this->rebuildRequest($request, $pathinfo));

            return $this->redirect($pathinfo, $parameters['_route'] ?? null) + $parameters;
        }
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH)
    {
        return $this->router->generate($name, $parameters, $referenceType);
    }

    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    public function getContext()
    {
        return $this->router->getContext();
    }

    public function redirect(string $path, string $route, string $scheme = null): array
    {
        return [
            '_controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction',
            'path' => $path,
            'permanent' => true,
            'scheme' => $scheme,
            'httpPort' => $this->router->getContext()->getHttpPort(),
            'httpsPort' => $this->router->getContext()->getHttpsPort(),
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
