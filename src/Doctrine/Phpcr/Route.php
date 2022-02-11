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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\PHPCR\Exception\InvalidArgumentException;
use Doctrine\ODM\PHPCR\HierarchyInterface;
use Symfony\Cmf\Bundle\RoutingBundle\Model\Route as RouteModel;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * PHPCR-ODM routes use their path for the url. They need to have set the
 * IdPrefix on loading to represent the correct URL.
 *
 * @author david.buchmann@liip.ch
 */
class Route extends RouteModel implements PrefixInterface, HierarchyInterface
{
    protected object $parent;

    /**
     * PHPCR node name.
     */
    protected string $name;

    /**
     * Child route documents.
     */
    protected Collection $children;

    /**
     * The part of the PHPCR path that does not belong to the url.
     *
     * This field is not persisted in storage.
     */
    protected ?string $idPrefix = null;

    /**
     * PHPCR id can not end on '/', so we need an additional option for a
     * trailing slash.
     *
     * Additional supported option is:
     *
     * * add_trailing_slash: When set, a trailing slash is appended to the route
     *
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->children = new ArrayCollection();
    }

    /**
     * Move the route by setting a parent.
     *
     * Note that this will change the URL this route matches.
     *
     * @param object $parent the new parent document
     */
    public function setParentDocument($parent): static
    {
        if (!\is_object($parent)) {
            throw new InvalidArgumentException('Parent must be an object '.\gettype($parent).' given.');
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * The parent document, which might be another route or some other
     * document.
     */
    public function getParentDocument(): object
    {
        return $this->parent;
    }

    /**
     * @deprecated For BC with the PHPCR-ODM 1.4 HierarchyInterface
     * @see setParentDocument
     */
    public function setParent($parent)
    {
        @trigger_error('The '.__METHOD__.'() method is deprecated and will be removed in version 4.0. Use setParentDocument() instead.', \E_USER_DEPRECATED);

        return $this->setParentDocument($parent);
    }

    /**
     * @deprecated For BC with the PHPCR-ODM 1.4 HierarchyInterface
     * @see getParentDocument
     */
    public function getParent()
    {
        @trigger_error('The '.__METHOD__.'() method is deprecated and will be removed in version 4.0. Use getParentDocument() instead.', \E_USER_DEPRECATED);

        return $this->getParentDocument();
    }

    /**
     * Rename a route by setting its new name.
     *
     * Note that this will change the URL this route matches.
     */
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
     * Convenience method to set parent and name at the same time.
     *
     * The url will be the url of the parent plus the supplied name.
     */
    public function setPosition(object $parent, string $name): static
    {
        if (!\is_object($parent)) {
            throw new InvalidArgumentException('Parent must be an object '.\gettype($parent).' given.');
        }

        $this->parent = $parent;
        $this->name = $name;

        return $this;
    }

    /**
     * PHPCR documents can be moved by setting the id to a new path.
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Overwritten to translate into a move operation.
     */
    public function setStaticPrefix(string $prefix): static
    {
        $this->id = str_replace($this->getStaticPrefix(), $prefix, $this->id);

        return $this;
    }

    public function getPrefix(): ?string
    {
        return $this->idPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrefix(string $prefix): static
    {
        $this->idPrefix = $prefix;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * Overwrite model method as we need to build this
     */
    public function getStaticPrefix(): string
    {
        $path = $this->getId();
        $prefix = $this->getPrefix();

        return $this->generateStaticPrefix($path, $prefix);
    }

    /**
     * @param string      $id       PHPCR id of this document
     * @param string|null $idPrefix part of the id that can be removed
     *
     * @return string the static part of the pattern of this route
     *
     * @throws \LogicException if there is no prefix or the prefix does not match
     */
    public function generateStaticPrefix(string $id, ?string $idPrefix): string
    {
        if ('' === $idPrefix || null === $idPrefix) {
            throw new \LogicException('Can not determine the prefix. Either this is a new, unpersisted document or the listener that calls setPrefix is not set up correctly.');
        }

        if (!str_starts_with($id, $idPrefix)) {
            throw new \LogicException("The id prefix '$idPrefix' does not match the route document path '$id'");
        }

        $url = substr($id, \strlen($idPrefix));
        if (!$url) {
            $url = '/';
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     *
     * Handle the trailing slash option.
     */
    public function getPath(): string
    {
        $pattern = parent::getPath();
        if ($this->getOption('add_trailing_slash') && '/' !== $pattern[\strlen($pattern) - 1]) {
            $pattern .= '/';
        }

        return $pattern;
    }

    /**
     * Get all route children of this route.
     *
     * Filters out children that do not implement the RouteObjectInterface.
     *
     * @return RouteObjectInterface[]
     */
    public function getRouteChildren(): array
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
     * Get all children of this route including non-routes.
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    protected function isBooleanOption(string $name): bool
    {
        return 'add_trailing_slash' === $name || parent::isBooleanOption($name);
    }
}
