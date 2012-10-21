<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Document;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\RedirectRouteInterface;

use Doctrine\Common\Collections\Collection;

/**
 * {@inheritDoc}
 */
class RedirectRoute extends Route implements RedirectRouteInterface
{
    /**
     * Absolute uri to redirect to
     */
    protected $uri;

    /**
     * The name of a target route (for use with standard symfony routes)
     */
    protected $routeName;

    /**
     * Target route document to redirect to different dynamic route
     */
    protected $routeTarget;

    /**
     * Whether this is a permanent redirect
     */
    protected $permanent;

    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection
     */
    protected $parameters;

    public function setRouteContent($document)
    {
        throw new \LogicException('Do not set a content for the redirect route. It is its own content.');
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteContent()
    {
        return $this;
    }

    /**
     * Set the route this redirection route points to
     */
    public function setRouteTarget(RouteObjectInterface $document)
    {
        $this->routeTarget = $document;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteTarget()
    {
        return $this->routeTarget;
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Set whether this redirection should be permanent or not.
     *
     * @param boolean $permanent
     */
    public function setPermanent($permanent)
    {
        $this->permanent = $permanent;
    }

    /**
     * {@inheritDoc}
     */
    public function isPermanent()
    {
        return $this->permanent;
    }

    /**
     * @param array $parameter a hashmap of key to value mapping for route
     *      parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters()
    {
        $parameters = $this->parameters;
        if ($parameters instanceof Collection) {
            $parameters = $parameters->toArray();
        }

        return $parameters;
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri()
    {
        return $this->uri;
    }
}
