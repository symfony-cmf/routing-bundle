<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Model;

use LogicException;
use Symfony\Cmf\Component\Routing\RedirectRouteInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * {@inheritdoc}
 */
class RedirectRoute extends Route implements RedirectRouteInterface
{
    /**
     * Absolute uri to redirect to.
     */
    protected ?string $uri = null;

    /**
     * The name of a target route (for use with standard symfony routes).
     */
    protected ?string $routeName = null;

    /**
     * Target route document to redirect to different dynamic route.
     */
    protected ?SymfonyRoute $routeTarget = null;

    /**
     * Whether this is a permanent redirect. Defaults to false.
     */
    protected bool $permanent = false;

    protected array $parameters = [];

    /**
     * Never call this, it makes no sense. The redirect route will return $this
     * as route content for the redirection controller to have the redirect route
     * object as content.
     *
     * @throws LogicException
     */
    public function setContent($document): static
    {
        throw new LogicException('Do not set a content for the redirect route. It is its own content.');
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(): static
    {
        return $this;
    }

    /**
     * Set the route this redirection route points to. This must be a PHPCR-ODM
     * mapped object.
     */
    public function setRouteTarget(?SymfonyRoute $document): void
    {
        $this->routeTarget = $document;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteTarget(): ?SymfonyRoute
    {
        return $this->routeTarget;
    }

    /**
     * Set a symfony route name for this redirection.
     */
    public function setRouteName(?string $routeName): void
    {
        $this->routeName = $routeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    /**
     * Set whether this redirection should be permanent or not. Default is
     * false.
     */
    public function setPermanent(bool $permanent): void
    {
        $this->permanent = $permanent;
    }

    /**
     * {@inheritdoc}
     */
    public function isPermanent(): bool
    {
        return $this->permanent;
    }

    /**
     * Set the parameters for building this route. Used with both route name
     * and target route document.
     *
     * @param array<string, string> $parameters a hashmap of key to value mapping for route parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set the absolute redirection target URI.
     */
    public function setUri(?string $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): ?string
    {
        return $this->uri;
    }
}
