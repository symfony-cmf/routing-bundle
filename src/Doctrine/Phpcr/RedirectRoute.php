<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\PHPCR\HierarchyInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Model\RedirectRoute as RedirectRouteModel;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * {@inheritdoc}
 *
 * This extends the RedirectRoute Model. We need to re-implement everything
 * that the PHPCR Route document adds.
 */
class RedirectRoute extends RedirectRouteModel implements PrefixInterface, HierarchyInterface
{
    /**
     * parent document.
     *
     * @var object
     */
    protected $parent;

    /**
     * PHPCR node name.
     *
     * @var string
     */
    protected $name;

    /**
     * Child route documents.
     *
     * @var Collection
     */
    protected $children;

    /**
     * The part of the PHPCR path that does not belong to the url.
     *
     * This field is not persisted in storage.
     *
     * @var string
     */
    protected $idPrefix;

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

        $this->children = [];
    }

    /**
     * Move the route by setting a parent.
     *
     * Note that this will change the URL this route matches.
     *
     * @param object $parent the new parent document
     *
     * @return $this
     */
    public function setParentDocument($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentDocument()
    {
        return $this->parent;
    }

    /**
     * @deprecated For BC with the PHPCR-ODM 1.4 HierarchyInterface
     * @see setParentDocument
     */
    public function setParent($parent)
    {
        @trigger_error('The '.__METHOD__.'() method is deprecated and will be removed in version 3.0. Use setParentDocument() instead.', E_USER_DEPRECATED);

        return $this->setParentDocument($parent);
    }

    /**
     * @deprecated For BC with the PHPCR-ODM 1.4 HierarchyInterface
     * @see getParentDocument
     */
    public function getParent()
    {
        @trigger_error('The '.__METHOD__.'() method is deprecated and will be removed in version 3.0. Use getParentDocument() instead.', E_USER_DEPRECATED);

        return $this->getParentDocument();
    }

    /**
     * Rename a route by setting its new name.
     *
     * Note that this will change the URL this route matches.
     *
     * @param string $name the new name
     *
     * @return self
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
     *
     * @param object $parent The parent document
     * @param string $name   The local name of this route
     *
     * @return self
     */
    public function setPosition($parent, $name)
    {
        $this->parent = $parent;
        $this->name = $name;

        return $this;
    }

    /**
     * PHPCR documents can be moved by setting the id to a new path.
     *
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setPrefix($idPrefix)
    {
        $this->idPrefix = $idPrefix;

        return $this;
    }

    /**
     * {@inheritdoc}
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
        if ('' === $idPrefix) {
            throw new \LogicException('Can not determine the prefix. Either this is a new, unpersisted document or the listener that calls setPrefix is not set up correctly.');
        }

        if (0 !== strpos($id, $idPrefix)) {
            throw new \LogicException("The id prefix '$idPrefix' does not match the route document path '$id'");
        }

        $url = substr($id, \strlen($idPrefix));
        if ('' === $url) {
            $url = '/';
        }

        return $url;
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
     * Return this routes children.
     *
     * Filters out children that do not implement
     * the RouteObjectInterface.
     *
     * @return array - array of RouteObjectInterface's
     */
    public function getRouteChildren()
    {
        $children = [];

        foreach ($this->children as $child) {
            if ($child instanceof RouteObjectInterface) {
                $children[] = $child;
            }
        }

        return $children;
    }

    /**
     * {@inheritdoc}
     */
    protected function isBooleanOption($name)
    {
        return 'add_trailing_slash' === $name || parent::isBooleanOption($name);
    }
}
