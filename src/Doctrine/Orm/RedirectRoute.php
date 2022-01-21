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

    protected string $serialisedParameters;

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

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the parameters for building this route. Used with both route name
     * and target route document.
     */
    public function setParameters(array $parameters): void
    {
        $this->serialisedParameters = json_encode($parameters, \JSON_THROW_ON_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        if (!isset($this->serialisedParameters)) {
            return [];
        }
        $params = json_decode($this->serialisedParameters, true, 512, \JSON_THROW_ON_ERROR);

        return \is_array($params) ? $params : [];
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function isBooleanOption(string $name): bool
    {
        return 'add_trailing_slash' === $name || parent::isBooleanOption($name);
    }
}
