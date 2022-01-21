<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Controller;

use Symfony\Cmf\Component\Routing\RedirectRouteInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Default router that handles redirection route objects.
 *
 * This is partially a duplication of Symfony\Bundle\FrameworkBundle\Controller\RedirectController
 * but we do not want a dependency on SymfonyFrameworkBundle just for this.
 *
 * The plus side is that with the route interface we do not need to pass the
 * parameters through magic request attributes.
 */
final class RedirectController
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function redirectAction(RedirectRouteInterface $contentDocument): RedirectResponse
    {
        $url = $contentDocument->getUri();

        if (empty($url)) {
            $routeTarget = $contentDocument->getRouteTarget();
            if ($routeTarget) {
                $url = $this->router->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, array_merge($contentDocument->getParameters(), [RouteObjectInterface::ROUTE_OBJECT => $routeTarget]), UrlGeneratorInterface::ABSOLUTE_URL);
            } else {
                $routeName = $contentDocument->getRouteName();
                $url = $this->router->generate($routeName, $contentDocument->getParameters(), UrlGeneratorInterface::ABSOLUTE_URL);
            }
        }

        return new RedirectResponse($url, $contentDocument->isPermanent() ? 301 : 302);
    }
}
