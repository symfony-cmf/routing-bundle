<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Entity;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;

use Symfony\Cmf\Bundle\RoutingBundle\Model\Route as BaseRoute;


use Doctrine\ORM\Mapping as ORM;


/**
 * ORM route version.
 * @author matteo caberlotto mcaber@gmail.com
 */
class Route extends baseRoute
{
    /**
     * {@inheritDoc}
     */
    protected $id_prefix = '';

    /**
     * {@inheritDoc}
     */
    protected $id;

    /**
     * {@inheritDoc}
     */
    protected $name;

    /**
     * {@inheritDoc}
     */
    protected $path;

    /**
     * {@inheritDoc}
     */
    protected $position;

    /**
     * {@inheritDoc}
     */
    protected $variablePattern;

    /**
     * {@inheritDoc}
     */
    protected $addFormatPattern;

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

    /**
     * This property is used to order routes during ORM query and thus
     * matching order.
     * (Higher position wins. i.e.: ORDER BY - DESC)
     */
    protected $priority;

    /**
     * Set priority
     *
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $priority;
    }

    /**
     * Returns the route priority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * {@inheritDoc}
     */
    public function getStaticPrefix()
    {
        return $this->path;
    }
}
