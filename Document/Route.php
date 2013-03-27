<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Document;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Default document for routing table entries that work with the DynamicRouter.
 *
 * This needs the IdPrefix service to run and setPrefix whenever a route is
 * loaded. Otherwise the static prefix can not be determined.
 *
 * @author david.buchmann@liip.ch
 */
class Route extends SymfonyRoute implements RouteObjectInterface
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
     * The full repository path to this route object
     *
     * @var string
     */
    protected $id;

    /**
     * The referenced document
     *
     * @var object
     */
    protected $routeContent;

    /**
     * @since Symfony 2.2 introduces the host name pattern. This default
     * implementation just stores it as a field.
     *
     * TODO: this could be removed if we would require 2.2 and map the host via the parent
     *
     * @var string
     */
    protected $host = '';

    /**
     * Variable pattern part. The static part of the pattern is the id without the prefix.
     *
     * @var string
     */
    protected $variablePattern;

    /**
     * if to add ".{_format}" to the pattern
     *
     * @var Boolean
     */
    protected $addFormatPattern;

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
     * Whether this route was changed since being last compiled.
     *
     * State information not persisted in storage.
     *
     * @var Boolean
     */
    protected $needRecompile = false;

    /**
     * Overwrite to be able to create route without pattern
     *
     * @param bool $addFormatPattern if to add ".{_format}" to the route pattern
     *                                  also implicitly sets a default/require on "_format" to "html"
     * @param bool $addTrailingSlash whether to add a trailing slash to the route, defaults to not add one
     */
    public function __construct($addFormatPattern = false, $addTrailingSlash = false)
    {
        $this->setDefaults(array());
        $this->setRequirements(array());
        $this->setOptions(array());
        $this->children = array();

        $this->addFormatPattern = $addFormatPattern;
        if ($this->addFormatPattern) {
            $this->setDefault('_format', 'html');
            $this->setRequirement('_format', 'html');
        }
        $this->addTrailingSlash = $addTrailingSlash;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteKey()
    {
        return $this->getId();
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
     * Get the repository path of this url entry
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getPrefix()
    {
        return $this->idPrefix;
    }

    public function setPrefix($idPrefix)
    {
        $this->idPrefix = $idPrefix;

        return $this;
    }

    /**
     * {@inheritDoc}
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
     * Set the document this url points to
     */
    public function setRouteContent($document)
    {
        $this->routeContent = $document;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteContent()
    {
        return $this->routeContent;
    }

    /**
     * Hide the core host name pattern
     *
     * TODO: this could be removed if we would require 2.2 and map the host via the parent
     */
    public function setHost($pattern)
    {
        $this->host = $pattern;

        return $this;
    }

    /**
     * Hide the core host name pattern
     *
     * TODO: this could be removed if we would require 2.2 and map the host via the parent
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritDoc}
     *
     * Prevent setting the default 'compiler_class' so that we do not persist it
     */
    public function setOptions(array $options)
    {
        return $this->addOptions($options);
    }

    /**
     * {@inheritDoc}
     *
     * Handling the missing default 'compiler_class'
     * @see setOptions
     */
    public function getOption($name)
    {
        $option = parent::getOption($name);
        if (null === $option && 'compiler_class' === $name) {
            return 'Symfony\\Component\\Routing\\RouteCompiler';
        }

        return $option;
    }

    /**
     * {@inheritDoc}
     *
     * Handling the missing default 'compiler_class'
     * @see setOptions
     */
    public function getOptions()
    {
        $options = parent::getOptions();
        if (!array_key_exists('compiler_class', $options)) {
            $options['compiler_class'] = 'Symfony\\Component\\Routing\\RouteCompiler';
        }

        return $options;
    }

    /**
     * TODO: remove when we drop support for symfony 2.1
     *
     * @deprecated compatibility with symfony 2.1
     */
    public function getPattern()
    {
        return $this->getPath();
    }

    /**
     * TODO: remove when we drop support for symfony 2.1
     *
     * @deprecated compatibility with symfony 2.1
     */
    public function setPattern($pattern)
    {
        return $this->setPath($pattern);
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        $pattern = $this->getStaticPrefix() . $this->getVariablePattern();
        if ($this->addFormatPattern && !preg_match('/(.+)\.[a-z]+$/i', $pattern, $matches)) {
            $pattern .= '.{_format}';
        };
        if ($this->addTrailingSlash && '/' !== $pattern[strlen($pattern)-1]) {
            $pattern .= '/';
        };

        return $pattern;
    }

    /**
     * {@inheritDoc}
     *
     * It is recommended to use setVariablePattern to just set the part after
     * the fixed part that follows from the repository id. If you use this
     * method, it will ensure the start of the pattern matches the repository
     * path (id) of this route document. Make sure to persist the route before
     * setting the pattern to have the id field initialized.
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
     * @return string the variable part of the url pattern
     */
    public function getVariablePattern()
    {
        return $this->variablePattern;
    }

    /**
     * @param string $variablePattern the variable part of the url pattern
     *
     * @return Route
     */
    public function setVariablePattern($variablePattern)
    {
        $this->variablePattern = $variablePattern;
        $this->needRecompile = true;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * Overwritten to make sure the route is recompiled if the pattern was changed
     */
    public function compile()
    {
        if ($this->needRecompile) {
            // calling parent::setPath just to let it set compiled=null. the parent $path field is never used
            // TODO: drop setPattern when we drop symfony 2.1 support
            // TODO: for now we need to check the method as setPattern on 2.2. triggers our setPath instead of parent setPath
            if (method_exists('Symfony\Component\Routing\Route', 'setPath')) {
                parent::setPath($this->getPath());
            } else {
                parent::setPattern($this->getPath());
            }
        }

        return parent::compile();
    }

    /**
     * Return this routes children
     *
     * Filters out children that do not implement
     * the RouteObjectInterface.
     *
     * @return array - array of RouteObjectInterface's
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

    public function __toString()
    {
        return (string) $this->name;
    }
}
