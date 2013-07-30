<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm;

use Symfony\Cmf\Bundle\RoutingBundle\Model\Route as RouteModel;

/**
 * ORM route version.
 * @author matteo caberlotto mcaber@gmail.com
 */
class Route extends RouteModel
{
    /**
     * {@inheritDoc}
     */
    protected $name;

    /**
     * {@inheritDoc}
     */
    protected $position;

    /**
     * {@inheritDoc}
     */
    protected $addTrailingSlash;

    /**
     * {@inheritDoc}
     */
    protected $host;

    /**
     * {@inheritDoc}
     */
    protected $defaults;

    /**
     * {@inheritDoc}
     */
    protected $requirements;

    /**
     * {@inheritDoc}
     */
    protected $options;
}