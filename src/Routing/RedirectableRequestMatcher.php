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
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class RedirectableRequestMatcher implements RequestMatcherInterface
{
    private RequestMatcherInterface $decorated;

    public function __construct(RequestMatcherInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * If the matcher can not find a match,
     * it will try to add or remove a trailing slash to the path and match again.
     */
    public function matchRequest(Request $request)
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

            $parameters = $this->decorated->matchRequest($this->rebuildRequest($request, $pathinfo));

            return $this->redirect($pathinfo, $parameters['_route'] ?? null) + $parameters;
        }
    }

    private function redirect(string $path, string $route): array
    {
        return [
            '_controller' => 'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction',
            'path' => $path,
            'permanent' => true,
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
