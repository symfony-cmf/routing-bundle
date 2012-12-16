<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Routing;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use Symfony\Cmf\Component\Routing\DynamicRouter as BaseDynamicRouter;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Symfony framework integration of the CMF routing component DynamicRouter class
 *
 * @author Filippo de Santis
 * @author David Buchmann
 * @author Lukas Smith
 * @author Nacho Martìn
 */
class DynamicRouter extends BaseDynamicRouter implements ContainerAwareInterface
{
    /**
     * key for the request attribute that contains the content document if this
     * route has one associated
     */
    const CONTENT_KEY = 'contentDocument';

    /**
     * key for the request attribute that contains the template this document
     * wants to use
     */
    const CONTENT_TEMPLATE = 'contentTemplate';

    /**
     * To get the request from, as its not available immediatly
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Put content and template name into the request attributes instead of the
     * route defaults.
     *
     * {@inheritDoc}
     *
     * The match should identify  a controller for symfony. This can either be
     * the fully qualified class name or the service name of a controller that
     * is registered as a service. In both cases, the action to call on that
     * controller is appended, separated with two colons.
     */
    public function match($url)
    {
        $defaults = parent::match($url);

        return $this->cleanDefaults($defaults);
    }

    public function matchRequest(Request $request)
    {
        $defaults = parent::matchRequest($request);

        return $this->cleanDefaults($defaults, $request);
    }

    /**
     * Clean up the match data and move some values into the request attributes.
     *
     * @param array $defaults The defaults from the match
     * @param Request $request The request object if available
     *
     * @return array the updated defaults to return for this match
     */
    protected function cleanDefaults($defaults, Request $request = null)
    {
        if (null === $request) {
            $request = $this->getRequest();
        }
        if (isset($defaults[RouteObjectInterface::CONTENT_OBJECT])) {
            $request->attributes->set(self::CONTENT_KEY, $defaults[RouteObjectInterface::CONTENT_OBJECT]);
            unset($defaults[RouteObjectInterface::CONTENT_OBJECT]);
        }

        if (isset($defaults[RouteObjectInterface::TEMPLATE_NAME])) {
            $request->attributes->set(self::CONTENT_TEMPLATE, $defaults[RouteObjectInterface::TEMPLATE_NAME]);
            unset($defaults[RouteObjectInterface::TEMPLATE_NAME]);
        }

        return $defaults;
    }

    public function getRequest()
    {
        if (!$request = $this->container->get('request')) {
            throw new ResourceNotFoundException('Request object not available from container');
        }
        return $request;
    }
}
