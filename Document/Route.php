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
     * children - needed for the admin to work ...
     *
     * @var Collection
     */
    protected $children;

    /**
     * The full repository path to this route object
     *
     * @var string
     */
    protected $path;

    /**
     * The referenced document
     *
     * @var object
     */
    protected $routeContent;

    /**
     * The part of the phpcr path that is not part of the url
     *
     * @var string
     */
    protected $idPrefix;

    /**
     * @since Symfony 2.2 introduces the host name pattern. This default
     * implementation just stores it as a field.
     *
     * TODO: this could be removed if we would require 2.2 and map the hostname via the parent
     *
     * @var string
     */
    protected $hostnamePattern = '';

    /**
     * Variable pattern part. The static part of the pattern is the id without the prefix.
     *
     * @var string
     */
    protected $variablePattern;

    /**
     * @var Boolean
     */
    protected $needRecompile = false;

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
     * Overwrite to be able to create route without pattern
     *
     * @param Boolean $addFormatPattern if to add ".{_format}" to the route pattern
     *                                  also implicitly sets a default/require on "_format" to "html"
     */
    public function __construct($addFormatPattern = false, $addTrailingSlash = false)
    {
        $this->setDefaults(array());
        $this->setRequirements(array());
        $this->setOptions(array());

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
        return $this->getPath();
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
    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
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
        return $this->generateStaticPrefix($this->getPath(), $this->idPrefix);
    }

    public function generateStaticPrefix($path, $idPrefix)
    {
        if (0 == strlen($idPrefix)) {
            throw new \LogicException('Can not determine the prefix. Either this is a new, unpersisted document or the listener that calls setPrefix is not set up correctly.');
        }

        if (strncmp($path, $idPrefix, strlen($idPrefix))) {
            throw new \LogicException("The id prefix '$idPrefix' does not match the route document path '$path'");
        }

        $url = substr($path, strlen($idPrefix));
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
     * Hide the core hostname pattern
     *
     * TODO: this could be removed if we would require 2.2 and map the hostname via the parent
     */
    public function setHostnamePattern($pattern)
    {
        $this->hostnamePattern = $pattern;
        return $this;
    }

    /**
     * Hide the core hostname pattern
     *
     * TODO: this could be removed if we would require 2.2 and map the hostname via the parent
     */
    public function getHostnamePattern()
    {
        return $this->hostnamePattern;
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
     * {@inheritDoc}
     */
    public function getPattern()
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
     * the fixed part that follows from the repository path. If you use this
     * method, it will ensure the start of the pattern matches the repository
     * path (id) of this route document. Make sure to persist the route before
     * setting the pattern to have the id field initialized.
     */
    public function setPattern($pattern)
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
            // calling parent::setPattern just to let it set compiled=null. the parent $pattern field is never used
            parent::setPattern($this->getStaticPrefix() . $this->getVariablePattern());
        }
        return parent::compile();
    }

    public function __toString()
    {
        return (string)$this->name;
    }
}