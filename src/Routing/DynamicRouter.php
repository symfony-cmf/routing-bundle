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

use Symfony\Cmf\Component\Routing\DynamicRouter as BaseDynamicRouter;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Symfony framework integration of the CMF routing component DynamicRouter class.
 *
 * @author Filippo de Santis
 * @author David Buchmann
 * @author Lukas Smith
 * @author Nacho Martìn
 */
class DynamicRouter extends BaseDynamicRouter
{
    /**
     * key for the request attribute that contains the route document.
     */
    public const ROUTE_KEY = 'routeDocument';

    /**
     * key for the request attribute that contains the content document if this
     * route has one associated.
     */
    public const CONTENT_KEY = 'contentDocument';

    /**
     * key for the request attribute that contains the template this document
     * wants to use.
     */
    public const CONTENT_TEMPLATE = 'template';

    private RequestStack $requestStack;

    /**
     * Put content and template name into the request attributes instead of the
     * route defaults.
     *
     * {@inheritdoc}
     *
     * The match should identify  a controller for symfony. This can either be
     * the fully qualified class name or the service name of a controller that
     * is registered as a service. In both cases, the action to call on that
     * controller is appended, separated with two colons.
     */
    public function match(string $url): array
    {
        $defaults = parent::match($url);

        return $this->cleanDefaults($defaults);
    }

    public function matchRequest(Request $request): array
    {
        $defaults = parent::matchRequest($request);

        return $this->cleanDefaults($defaults, $request);
    }

    /**
     * Clean up the match data and move some values into the request attributes.
     *
     * @param array<string, string> $defaults The defaults from the match
     * @param Request|null          $request  The request object if available
     *
     * @return array<string, string> the updated defaults to return for this match
     */
    protected function cleanDefaults(array $defaults, Request $request = null): array
    {
        if (null === $request) {
            $request = $this->getRequest();
        }

        if (\array_key_exists(RouteObjectInterface::ROUTE_OBJECT, $defaults)) {
            $request->attributes->set(self::ROUTE_KEY, $defaults[RouteObjectInterface::ROUTE_OBJECT]);
            unset($defaults[RouteObjectInterface::ROUTE_OBJECT]);
        }

        if (\array_key_exists(RouteObjectInterface::CONTENT_OBJECT, $defaults)) {
            $request->attributes->set(self::CONTENT_KEY, $defaults[RouteObjectInterface::CONTENT_OBJECT]);
            unset($defaults[RouteObjectInterface::CONTENT_OBJECT]);
        }

        if (\array_key_exists(RouteObjectInterface::TEMPLATE_NAME, $defaults)) {
            $request->attributes->set(self::CONTENT_TEMPLATE, $defaults[RouteObjectInterface::TEMPLATE_NAME]);

            unset($defaults[RouteObjectInterface::TEMPLATE_NAME]);
        }

        return $defaults;
    }

    /**
     * Set the request stack so that we can find the current request.
     */
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Get the current request from the request stack.
     *
     * @throws ResourceNotFoundException
     */
    public function getRequest(): Request
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest) {
            throw new ResourceNotFoundException('There is no request in the request stack');
        }

        return $currentRequest;
    }
}
