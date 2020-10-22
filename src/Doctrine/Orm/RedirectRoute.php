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

use Symfony\Cmf\Bundle\RoutingBundle\Model\RedirectRoute as RedirectRouteModel;

/**
 * {@inheritdoc}
 *
 * Provides a redirect route stored in the Doctrine ORM and used as content for generic route to provide redirects
 */
class RedirectRoute extends RedirectRouteModel
{
    /**
     * Unique id of this route.
     *
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $serialisedParameters;

    /**
     * Overwrite to be able to create route without pattern.
     *
     * Additional options:
     *
     * * add_trailing_slash: When set, a trailing slash is appended to the route
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the parameters for building this route. Used with both route name
     * and target route document.
     */
    public function setParameters(array $parameters)
    {
        $this->serialisedParameters = json_encode($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $params = json_decode($this->serialisedParameters);

        return \is_array($params) ? $params : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        $pattern = parent::getPath();
        if ($this->getOption('add_trailing_slash') && '/' !== $pattern[\strlen($pattern) - 1]) {
            $pattern .= '/';
        }

        return $pattern;
    }

    /**
     * {@inheritdoc}
     */
    protected function isBooleanOption($name)
    {
        return 'add_trailing_slash' === $name || parent::isBooleanOption($name);
    }
}
