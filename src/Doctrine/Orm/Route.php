<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Model\Route as RouteModel;

/**
 * The ORM route version.
 *
 * @author matteo caberlotto <mcaber@gmail.com>
 * @author Wouter J <waldio.webdesign@gmail.com>
 */
class Route extends RouteModel
{
    protected string $name = '';

    /**
     * Sort order of this route when it is returned by the route provider.
     */
    protected int $position = 0;

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the sort order of this route.
     */
    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get the sort order of this route.
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteKey(): string
    {
        return $this->getName();
    }
}
