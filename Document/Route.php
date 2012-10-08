<?php

namespace Symfony\Cmf\Bundle\RoutingExtraBundle\Document;

use Symfony\Component\Routing\Route as SymfonyRoute;
use Doctrine\Common\Collections\Collection;
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
     */
    protected $parent;
    /**
     * PHPCR node name
     */
    protected $name;

    /**
     * children - needed for the admin to work ...
     */
    protected $children;

    /**
     * The full repository path to this route object
     */
    protected $path;

    /**
     * The referenced document
     */
    protected $routeContent;

    /**
     * The part of the phpcr path that is not part of the url
     * @var string
     */
    protected $idPrefix;

    /**
     * Variable pattern part. The static part of the pattern is the id without the prefix.
     */
    protected $variablePattern;

    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection|array
     */
    protected $defaults = array();

    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection|array
     */
    protected $requirements = array();

    /**
     * @var \Doctrine\ODM\PHPCR\MultivaluePropertyCollection|array
     */
    protected $options = array();

    protected $needRecompile = false;

    protected $addFormatPattern;

    /**
     * Overwrite to be able to create route without pattern
     *
     * @param Boolean $addFormatPattern if to add ".{_format}" to the route pattern
     *                                  also implicitly sets a default/require on "_format" to "html"
     */
    public function __construct($addFormatPattern = false)
    {
        $this->postLoad();

        $this->addFormatPattern = $addFormatPattern;
        if ($this->addFormatPattern) {
            $this->setDefault('_format', 'html');
            $this->setRequirement('_format', 'html');
        }
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
    }

    public function getPrefix()
    {
        return $this->idPrefix;
    }

    public function setPrefix($idPrefix)
    {
        $this->idPrefix = $idPrefix;
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
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteContent()
    {
        return $this->routeContent;
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

    /**
     * copy defaults/requirements/options to the parent class
     */
    public function postLoad()
    {
        $defaults = $this->defaults instanceof Collection
            ? $this->defaults->toArray() : $this->defaults;
        $this->setDefaults($defaults);

        $requirements = $this->requirements instanceof Collection
            ? $this->requirements->toArray() : $this->requirements;
        $this->setRequirements($requirements);

        $options = $this->options instanceof Collection
            ? $this->options->toArray() : $this->options;
        $this->setOptions($options);
    }

    /**
     * copy defaults/requirements/options from the parent class
     */
    public function preStorage()
    {
        $defaults = parent::getDefaults();
        $oldDefaults = $this->defaults instanceof Collection
            ? $this->defaults->toArray() : $this->defaults;
        if ($defaults !== $oldDefaults) {
            $this->defaults = $defaults;
        }

        $requirements = parent::getRequirements();
        $oldRequirements = $this->requirements instanceof Collection
            ? $this->requirements->toArray() : $this->requirements;
        if ($requirements !== $oldRequirements) {
            $this->requirements = $requirements;
        }

        $options = parent::getOptions();
        // avoid storing the default value for the compiler, in case this ever changes in code
        // would be nice if those where class constants of the symfony route instead of hardcoded strings
        if ('Symfony\\Component\\Routing\\RouteCompiler' == $options['compiler_class']) {
            unset($options['compiler_class']);
        }
        $oldOptions = $this->options instanceof Collection
            ? $this->options->toArray() : $this->options;
        if ($options !== $oldOptions) {
            $this->options = $options;
        }
    }

    public function __toString()
    {
        return (string)$this->name;
    }
}
