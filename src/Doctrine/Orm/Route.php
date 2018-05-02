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
    protected $name;

    /**
     * @var int sort order of this route when it is returned by the route provider
     */
    protected $position = 0;

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the sort order of this route.
     *
     * @param int $position
     *
     * @return self
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get the sort order of this route.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
