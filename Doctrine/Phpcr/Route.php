<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2013 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\PHPCR\Document\Generic;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Model\Route as RouteModel;

/**
 * PHPCR-ODM routes use their path for the url. They need to have set the
 * IdPrefix on loading to represent the correct URL.
 *
 * @author david.buchmann@liip.ch
 */
class Route extends RouteModel implements PrefixInterface
{
    /**
     * parent document
     *
     * @var object
     */
    protected $parent;

    /**
     * PHPCR node name
     *
     * @var string
     */
    protected $name;

    /**
     * Child route documents
     *
     * @var Collection
     */
    protected $children;

    /**
     * if to add "/" to the pattern
     *
     * @var Boolean
     */
    protected $addTrailingSlash;

    /**
     * The part of the PHPCR path that does not belong to the url
     *
     * This field is not persisted in storage.
     *
     * @var string
     */
    protected $idPrefix;

    /**
     * Overwrite to be able to create route without pattern
     *
     * @param Boolean $addFormatPattern if to add ".{_format}" to the route pattern
     *                                  also implicitly sets a default/require on "_format" to "html"
     * @param Boolean $addTrailingSlash whether to add a trailing slash to the route, defaults to not add one
     */
    public function __construct($addFormatPattern = false, $addTrailingSlash = false)
    {
        parent::__construct($addFormatPattern);

        $this->children = array();
        $this->addTrailingSlash = $addTrailingSlash;
    }

    public function getAddTrailingSlash()
    {
        return $this->addTrailingSlash;
    }

    public function setAddTrailingSlash($addTrailingSlash)
    {
        $this->addTrailingSlash = $addTrailingSlash;
    }

    /**
     * Move the route by setting a parent.
     *
     * Note that this will change the URL this route matches.
     *
     * @param object $parent the new parent document
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * The parent document, which might be another route or some other
     * document.
     *
     * @return Generic object
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Rename a route by setting its new name.
     *
     * Note that this will change the URL this route matches.
     *
     * @param string $name the new name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Convenience method to set parent and name at the same time.
     *
     * The url will be the url of the parent plus the supplied name.
     */
    public function setPosition($parent, $name)
    {
        $this->parent = $parent;
        $this->name = $name;

        return $this;
    }

    /**
     * PHPCR documents can be moved by setting the id to a new path.
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Overwritten to translate into a move operation.
     */
    public function setStaticPrefix($prefix)
    {
        $this->id = str_replace($this->getStaticPrefix(), $prefix, $this->id);
    }

    public function getPrefix()
    {
        return $this->idPrefix;
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix($idPrefix)
    {
        $this->idPrefix = $idPrefix;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Overwrite model method as we need to build this
     */
    public function getStaticPrefix()
    {
        return $this->generateStaticPrefix($this->getId(), $this->idPrefix);
    }

    /**
     * @param string $id       PHPCR id of this document
     * @param string $idPrefix part of the id that can be removed
     *
     * @return string the static part of the pattern of this route
     *
     * @throws \LogicException if there is no prefix or the prefix does not match
     */
    public function generateStaticPrefix($id, $idPrefix)
    {
        if (0 == strlen($idPrefix)) {
            throw new \LogicException('Can not determine the prefix. Either this is a new, unpersisted document or the listener that calls setPrefix is not set up correctly.');
        }

        if (strncmp($id, $idPrefix, strlen($idPrefix))) {
            throw new \LogicException("The id prefix '$idPrefix' does not match the route document path '$id'");
        }

        $url = substr($id, strlen($idPrefix));
        if (empty($url)) {
            $url = '/';
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        $pattern = parent::getPath();
        if ($this->addTrailingSlash && '/' !== $pattern[strlen($pattern)-1]) {
            $pattern .= '/';
        };

        return $pattern;
    }

    /**
     * {@inheritDoc}
     */
    public function setPath($pattern)
    {
        $len = strlen($this->getStaticPrefix());

        if (strncmp($this->getStaticPrefix(), $pattern, $len)) {
            throw new \InvalidArgumentException('You can not set a pattern for the route document that does not match its repository path. First move it to the correct path.');
        }

        return $this->setVariablePattern(substr($pattern, $len));
    }

    /**
     * Get all route children of this route.
     *
     * Filters out children that do not implement the RouteObjectInterface.
     *
     * @return RouteObjectInterface[]
     *
     */
    public function getRouteChildren()
    {
        $children = array();

        foreach ($this->children as $child) {
            if ($child instanceof RouteObjectInterface) {
                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * Get all children of this route including non-routes.
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }
}
