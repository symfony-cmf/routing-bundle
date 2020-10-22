<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Model;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;
use Symfony\Component\Routing\RouteCompiler;

/**
 * Default model for routing table entries that work with the DynamicRouter.
 *
 * @author david.buchmann@liip.ch
 */
class Route extends SymfonyRoute implements RouteObjectInterface
{
    /**
     * Unique id of this route.
     *
     * @var string
     */
    protected $id;

    /**
     * The referenced content object.
     *
     * @var object
     */
    protected $content;

    /**
     * Part of the URL that does not have parameters and thus can be used to
     * naivly guess candidate routes.
     *
     * Note that this field is not used by PHPCR-ODM
     *
     * @var string
     */
    protected $staticPrefix;

    /**
     * Variable pattern part. The static part of the pattern is the id without the prefix.
     *
     * @var string
     */
    protected $variablePattern;

    /**
     * Whether this route was changed since being last compiled.
     *
     * State information not persisted in storage.
     *
     * @var bool
     */
    protected $needRecompile = false;

    /**
     * Overwrite to be able to create route without pattern.
     *
     * Additional supported options are:
     *
     * * add_format_pattern: When set, ".{_format}" is appended to the route pattern.
     *                       Also implicitly sets a default/require on "_format" to "html".
     * * add_locale_pattern: When set, "/{_locale}" is prepended to the route pattern.
     */
    public function __construct(array $options = [])
    {
        $this->setDefaults([]);
        $this->setRequirements([]);
        $this->setOptions($options);

        if ($this->getOption('add_format_pattern')) {
            $this->setDefault('_format', 'html');
            $this->setRequirement('_format', 'html');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteKey()
    {
        return $this->getId();
    }

    /**
     * Get the repository path of this url entry.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string the static prefix part of this route
     */
    public function getStaticPrefix()
    {
        return $this->staticPrefix;
    }

    /**
     * @param string $prefix The static prefix part of this route
     *
     * @return Route $this
     */
    public function setStaticPrefix($prefix)
    {
        $this->staticPrefix = $prefix;

        return $this;
    }

    /**
     * Set the object this url points to.
     *
     * @param mixed $object A content object that can be persisted by the
     *                      storage layer
     *
     * @return self
     */
    public function setContent($object)
    {
        $this->content = $object;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     *
     * Prevent setting the default 'compiler_class' so that we do not persist it
     */
    public function setOptions(array $options)
    {
        return $this->addOptions($options);
    }

    /**
     * {@inheritdoc}
     *
     * Handling the missing default 'compiler_class'
     *
     * @see setOptions
     */
    public function getOption($name)
    {
        $option = parent::getOption($name);
        if (null === $option && 'compiler_class' === $name) {
            return RouteCompiler::class;
        }
        if ($this->isBooleanOption($name)) {
            return (bool) $option;
        }

        return $option;
    }

    /**
     * {@inheritdoc}
     *
     * Handling the missing default 'compiler_class'
     *
     * @see setOptions
     */
    public function getOptions()
    {
        $options = parent::getOptions();
        if (!\array_key_exists('compiler_class', $options)) {
            $options['compiler_class'] = RouteCompiler::class;
        }
        foreach ($options as $key => &$value) {
            if ($this->isBooleanOption($key)) {
                $value = (bool) $value;
            }
        }

        return $options;
    }

    /**
     * Helper method to check if an option is a boolean option to allow better forms.
     *
     * @param string $name
     *
     * @return bool whether $name is a boolean option
     */
    protected function isBooleanOption($name)
    {
        return \in_array($name, ['add_format_pattern', 'add_locale_pattern']);
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        $pattern = '';
        if ($this->getOption('add_locale_pattern')) {
            $pattern .= '/{_locale}';
        }
        $pattern .= $this->getStaticPrefix();
        $pattern .= $this->getVariablePattern();
        if ($this->getOption('add_format_pattern') && !preg_match('/(.+)\.[a-z]+$/i', $pattern, $matches)) {
            $pattern .= '.{_format}';
        }

        return $pattern;
    }

    /**
     * {@inheritdoc}
     *
     * It is recommended to use setVariablePattern to just set the part after
     * the static part. If you use this method, it will ensure that the
     * static part is not changed and only change the variable part.
     *
     * When using PHPCR-ODM, make sure to persist the route before calling this
     * to have the id field initialized.
     */
    public function setPath($pattern)
    {
        if (0 !== strpos($pattern, $this->getStaticPrefix())) {
            throw new \InvalidArgumentException(sprintf(
                'You can not set pattern "%s" for this route with a static prefix of "%s". First update the static prefix or directly use setVariablePattern.',
                $pattern,
                $this->getStaticPrefix()
            ));
        }

        return $this->setVariablePattern(substr($pattern, \strlen($this->getStaticPrefix())));
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
     * {@inheritdoc}
     *
     * Overwritten to make sure the route is recompiled if the pattern was changed
     */
    public function compile()
    {
        if ($this->needRecompile) {
            // calling parent::setPath just to let it set compiled=null. the parent $path field is never used
            parent::setPath($this->getPath());
        }

        return parent::compile();
    }
}
